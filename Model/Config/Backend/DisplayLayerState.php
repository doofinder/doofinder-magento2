<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Config\Backend;

use Doofinder\Feed\Errors\ApiClient\InvalidApiKey;
use Doofinder\Feed\Helper\StoreConfig;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\App\ScopeInterface as DefaultScopeInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Http\Message\ResponseInterface;

class DisplayLayerState extends ConfigValue
{
    /** @var StoreRepositoryInterface */
    private $storeRepository;

    /** @var GuzzleClient  */
    private $guzzleClient;

    /** @var StoreConfig */
    private $storeConfig;

    public function __construct(
        StoreRepositoryInterface $storeRepository,
        GuzzleClient $guzzleClient,
        StoreConfig $storeConfig,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeRepository = $storeRepository;
        $this->guzzleClient = $guzzleClient;
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave(): DisplayLayerState
    {
        try {
            $stores = [];
            if ($this->getScope() == DefaultScopeInterface::SCOPE_DEFAULT) {
                $stores = $this->storeConfig->getAllStores();
            } elseif ($this->getScope() == ScopeInterface::SCOPE_WEBSITE ||
                $this->getScope() == ScopeInterface::SCOPE_WEBSITES) {
                $stores = $this->storeConfig->getWebsiteStores((int)$this->getScopeId());
            } elseif ($this->getScope() == ScopeInterface::SCOPE_STORE ||
                $this->getScope() == ScopeInterface::SCOPE_STORES) {
                $stores[] = $this->storeRepository->getById($this->getScopeId());
            }
            if (count($stores)) {
                $store = array_shift($stores);
                $hashId = $this->storeConfig->getHashId((int)$store->getId());
                if ($hashId) {
                    $payload = [
                        'search_engine' => $hashId,
                        'state' => (bool)$this->getValue(),
                    ];
                    try {
                        $this->post($payload);
                    } catch (\Exception $e) {
                        $this->_logger->error($e->getMessage());
                    }
                } else {
                    $this->_logger->info('Store with ID: ' . $store->getId() . ' does not have Hash ID configured.');
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return parent::afterSave();
    }

    /**
     * Make POST request
     *
     * @param array $body
     * @return string
     * @throws InvalidApiKey
     */
    private function post(array $body): string
    {
        $response = $this->guzzleClient->post(
            $this->storeConfig->getDisplayLayerStateEndpoint(),
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
}
