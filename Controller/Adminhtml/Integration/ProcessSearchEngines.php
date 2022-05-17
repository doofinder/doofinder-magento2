<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\SearchEngine;
use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ProcessSearchEngines extends Action implements HttpGetActionInterface
{
    private const INSTALLING_LOOP_STEP = 4;

    /** @var StoreConfig */
    private $storeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /** @var SearchEngine */
    private $searchEngineHelper;

    /** @var WriterInterface */
    private $configWriter;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var Escaper */
    protected $escaper;

    /** @var TypeListInterface */
    private $cacheTypeList;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        StoreConfig $storeConfig,
        StoreManagerInterface $storeManager,
        SearchEngine $searchEngineHelper,
        WriterInterface $configWriter,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        TypeListInterface $cacheTypeList,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->storeConfig = $storeConfig;
        $this->storeManager = $storeManager;
        $this->searchEngineHelper = $searchEngineHelper;
        $this->configWriter = $configWriter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->cacheTypeList = $cacheTypeList;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     *
     * @throws WebapiException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            foreach ($this->storeConfig->getAllStores() as $store) {
                $hashId = $this->storeConfig->getHashId((int)$store->getId());
                $this->searchEngineHelper->processSearchEngine($hashId, $this->getProcessCallbackUrl($store));
            }
            $resultJson->setData(true);
            $this->cacheTypeList->invalidate(Config::TYPE_IDENTIFIER);
        } catch (Exception $e) {
            $this->storeConfig->setInstallingLoopStatus(self::INSTALLING_LOOP_STEP);
            $this->logger->error('Initial Setup error: ' . $e->getMessage());
            $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
            $resultJson->setData(__('Process Search Engines'));
        }

        return $resultJson;
    }

    /**
     * Get Process Callback URL
     *
     * @param StoreInterface $store
     *
     * @return string
     */
    private function getProcessCallbackUrl(StoreInterface $store): string
    {
        return $store->getBaseUrl() . 'doofinderfeed/setup/processCallback';
    }
}
