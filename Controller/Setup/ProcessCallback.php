<?php declare(strict_types=1);

namespace Doofinder\Feed\Controller\Setup;

use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
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

    /** @var TypeListInterface */
    private $cacheTypeList;

    public function __construct(
        StoreConfig $storeConfig,
        UrlInterface $url,
        JsonSerializer $serializer,
        LoggerInterface $logger,
        TypeListInterface $cacheTypeList,
        Context $context
    ) {
        $this->storeConfig = $storeConfig;
        $this->url = $url;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->cacheTypeList = $cacheTypeList;
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
            $response = $this->sanitizeResponse($response);
            $this->logger->debug('[ProcessCallback] Status: ' . $response['status'] . ' - Message: ' . $response['message']);
            $displayLayerEnabled = $this->validateProcessMessage($response['message']);
            $this->logger->debug('[ProcessCallback] Display layer enabled: ' . ($displayLayerEnabled ? 1 : 0));
            $store = $this->getStoreFromRequestUrl();
            if (!$store || !$store->getId()) {
                throw new \Exception('There is no store view matching URL: '. $this->url->getCurrentUrl());
            }
            $this->logger->debug(('[ProcessCallback] Current URL: ' . $this->url->getCurrentUrl() . ' Store URL: ' . $store->getBaseUrl() . 'doofinderfeed/setup/processCallback'));
            $this->storeConfig->setGlobalDisplayLayerEnabled($displayLayerEnabled, (int)$store->getId());
            $this->cacheTypeList->invalidate(Config::TYPE_IDENTIFIER);
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
        return [
            'status' => $processCallbackStatus['status'] ?? '',
            'message' => strtolower(trim($processCallbackStatus['message'] ?? '', ' .'))
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
        return $message == 'sources were processed successfully' || $message == 'no changes in feeds';
    }

    /**
     * Get store view from request URL
     *
     * @return StoreInterface|null
     * @throws NoSuchEntityException
     */
    private function getStoreFromRequestUrl(): ?StoreInterface
    {
        $currentUrl = $this->url->getCurrentUrl();
        foreach ($this->storeConfig->getAllStores() as $store) {
            $storeUrl =  $store->getBaseUrl() . 'doofinderfeed/setup/processCallback';
            if ($storeUrl == $currentUrl) {
                return $store;
            }
        }

        return null;
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
}
