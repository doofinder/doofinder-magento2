<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Errors\ApiClient\InvalidApiKey;
use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Store\Model\ScopeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Create display layers
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateDisplayLayers extends Action implements HttpGetActionInterface
{
    private const INSTALLING_LOOP_STEP = 5;

    /** @var GuzzleClient  */
    private $guzzleClient;

    /** @var StoreConfig */
    private $storeConfig;

    /** @var WriterInterface */
    private $configWriter;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var Escaper */
    protected $escaper;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        GuzzleClient $guzzleClient,
        StoreConfig $storeConfig,
        WriterInterface $configWriter,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->guzzleClient = $guzzleClient;
        $this->storeConfig = $storeConfig;
        $this->configWriter = $configWriter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            foreach ($this->storeConfig->getAllWebsites() as $website) {
                $payload = [
                    'config' => [
                        'defaults' => [],
                        'search_engines' => [],
                    ],
                ];
                $stores = $this->storeConfig->getWebsiteStores((int)$website->getId());
                $index = 0;
                foreach ($stores as $store) {
                    if ($store->getIsActive()) {
                        $hashId = $this->storeConfig->getHashId((int)$store->getId());
                        if ($hashId) {
                            $localeCode = strtoupper($this->storeConfig->getLanguageFromStore($store));
                            $currencyCode = strtoupper($store->getCurrentCurrency()->getCode());
                            if (isset($payload['config']['search_engines'][$localeCode][$currencyCode])) {
                                throw new Exception(
                                    'Store View with the same locale and currency already exists in website. Store: ' . $store->getCode() . '. Locale code: ' . $localeCode
                                );
                            }
                            $payload['config']['search_engines'][$localeCode][$currencyCode] = $hashId;
                            if ($index === 0) {
                                $payload['config']['defaults'] = [
                                    "currency"    => $currencyCode,
                                    "language"    => $localeCode,
                                    "hashid"      => $hashId,
                                    "query_input" => "#search",
                                ];
                            }
                            $index++;
                        }
                    }
                }
                $response = \Zend_Json::decode($this->post($payload));
                if (!isset($response['script']) || !is_string($response['script'])) {
                    throw new Exception('Invalid create display layers response.');
                }
                $script = $response['script'];
                $installationId = $this->getInstallationId($script);
                $this->configWriter->save(StoreConfig::DISPLAY_LAYER_INSTALLATION_ID, $installationId, ScopeInterface::SCOPE_WEBSITES, $website->getId());
                $this->configWriter->save(StoreConfig::DISPLAY_LAYER_SCRIPT_CONFIG, $script, ScopeInterface::SCOPE_WEBSITES, $website->getId());
            }
            $resultJson->setData(true);
        } catch (Exception $e) {
            $this->storeConfig->setInstallingLoopStatus(self::INSTALLING_LOOP_STEP);
            $this->logger->error('Initial Setup error: ' . $e->getMessage());
            $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
            $resultJson->setData(__('Create Display Layers'));
        }

        return $resultJson;
    }

    private function post(array $body): string
    {
        $response = $this->guzzleClient->post(
            $this->storeConfig->getDisplayLayerCreateEndpoint(),
            [
                RequestOptions::VERIFY  => false,
                RequestOptions::HEADERS => [
                    'Authorization' => 'Token ' . $this->getAuthToken(),
                ],
                RequestOptions::JSON    => $body,
            ]
        );

        return $this->getResult($response);
    }

    /**
     * Get authentication token from API Key
     *
     * @return string
     * @throws InvalidApiKey
     */
    private function getAuthToken(): string
    {
        $apiKey = $this->storeConfig->getApiKey();
        $clusterToken = explode('-', $apiKey);
        if (count($clusterToken) != 2) {
            throw new InvalidApiKey("Invalid API Key provided");
        }

        return $clusterToken[1];
    }

    private function responseIsOk(?ResponseInterface $response): bool
    {
        if ($response) {
            $responseCode = (string)$response->getStatusCode();
            return substr($responseCode, 0, 1) === '2';
        }

        return false;
    }

    private function getResult(?ResponseInterface $response): string
    {
        if (!$this->responseIsOk($response)) {
            throw new TransferException('There was an error in create display layers request');
        }

        return $response->getBody()->getContents();
    }

    private function getInstallationId(string $script): string
    {
        $installationId = '';
        if (preg_match('/installationId:\s\'(.*)\'/', $script, $matches)) {
            $installationId = $matches[1];
        }

        return $installationId;
    }
}
