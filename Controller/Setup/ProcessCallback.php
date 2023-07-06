<?php declare(strict_types=1);

namespace Doofinder\Feed\Controller\Setup;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\Indexation;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Psr\Log\LoggerInterface;

class ProcessCallback extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /** @var StoreConfig */
    private $storeConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Serializer interface instance.
     *
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /** @var Pool */
    protected $cacheFrontendPool;

    /**
     * ProcessCallback constructor.
     *
     * @param StoreConfig $storeConfig
     * @param UrlInterface $url
     * @param JsonSerializer $serializer
     * @param LoggerInterface $logger
     * @param Pool $cacheFrontendPool
     * @param Context $context
     */
    public function __construct(
        StoreConfig $storeConfig,
        UrlInterface $url,
        JsonSerializer $serializer,
        LoggerInterface $logger,
        Pool $cacheFrontendPool,
        Context $context
    ) {
        $this->storeConfig = $storeConfig;
        $this->url = $url;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {

            $content = $this->getRequest()->getContent();
            $this->logger->debug('[ProcessCallback] Body:');
            $this->logger->debug($content);
            $response = $this->serializer->unserialize($content);
            $store = $this->getStoreFromRequest();
            if (!$store || !$store->getId()) {
                throw new \Exception('There is no store view matching URL: '. $this->url->getCurrentUrl());
            }
            $response = $this->sanitizeResponse($response);
            $this->logger->debug('[ProcessCallback] Status: ' . $response['status'] . ' - Message: ' . $response['result']);
            $this->logger->debug('[ProcessCallback] Display layer enabled: ' . ($response['error'] ? 0 : 1));
            $this->storeConfig->setIndexationStatus($response, (int)$store->getId());
            $this->storeConfig->setGlobalDisplayLayerEnabled(!$response['error'], (int)$store->getId());
            $this->cleanCache();
            $result->setData('OK');
        } catch (Exception $e) {
            $this->logger->error('[ProcessCallback] Error: ' . $e->getMessage());
            $result->setData('KO: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Sanitize process callback body
     *
     * @param array $processCallbackStatus
     *
     * @return array
     */
    private function sanitizeResponse(array $processCallbackStatus): array
    {
        $status_boolean = $this->validateProcessMessage($processCallbackStatus['message']);
        $date = new \DateTime();
        return [
            'status' => $status_boolean ? Indexation::DOOFINDER_INDEX_PROCESS_STATUS_SUCCESS : Indexation::DOOFINDER_INDEX_PROCESS_STATUS_FAILURE,
            'result' => strtolower(trim($processCallbackStatus['message'] ?? '', ' .')),
            'finished_at' => $date->format('M j, Y, g:i A'),
            'error' => !$status_boolean,
            'error_message' => !$status_boolean ? 'Check doofinder panel for more information about the error.' : '',
        ];
    }

    /**
     * Validate process message
     *
     * @param string $message
     *
     * @return bool
     */
    private function validateProcessMessage(string $message): bool
    {
        return $message == 'Sources were processed successfully.' || $message == 'No changes in feeds.';
    }

    /**
     * Get store view from request URL
     *
     * @return StoreInterface|null
     * @throws NoSuchEntityException
     */
    private function getStoreFromRequest(): ?StoreInterface
    {
        $params = $this->getRequest()->getParams();
        return $this->storeConfig->getStoreById($params["storeId"]);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * We need to clean the cache to avoid problems with the Doofinder Index Processing Status view
     */
    private function cleanCache()
    {
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
