<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer\Fulltext\Product;

use Exception;
use Magento\Catalog\Model\Product\Price\BasePriceStorage;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Doofinder\Feed\Model\Indexer\IndexerHandlerFactory;
use Doofinder\Feed\Registry\IndexerScope;
use Doofinder\Feed\Model\ChangedProduct\Registration;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Helper\StoreConfig;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Doofinder\Feed\Helper\Logger;

/**
 * Catalog search indexer plugin for catalog product used to register product
 * updates when catalogsearch index update mode is set to "on schedule".
 */
class Price extends AbstractPlugin
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var Registration
     */
    private $registration;
    /**
     * @var StoreConfig
     */
    private $storeConfig;
    /**
     * @var IndexerScope
     */
    private $indexerScope;
    /**
     * @var IndexerHelper
     */
    private $indexerHelper;
    /**
     * @var FulltextResource
     */
    private $fulltextResource;
    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;
    /**
     * @var FullFactory
     */
    private $fullActionFactory;

    /**
     * logger
     *
     * @var mixed
     */
    private $logger;

    /**
     * doofinderLogger
     *
     * @var mixed
     */
    private $doofinderLogger;


    /**
     * @param Registration $registration
     * @param StoreConfig $storeConfig
     * @param IndexerRegistry $indexerRegistry
     * @param ProductFactory $productFactory
     * @param IndexerScope $indexerScope
     * @param IndexerHelper $indexerHelper
     * @param FulltextResource $fulltextResource
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param ConfigInterface $config
     * @param FullFactory $fullActionFactory
     */
    public function __construct(Registration          $registration,
                                StoreConfig           $storeConfig,
                                IndexerRegistry       $indexerRegistry,
                                ProductFactory        $productFactory,
                                IndexerScope          $indexerScope,
                                IndexerHelper         $indexerHelper,
                                FulltextResource      $fulltextResource,
                                IndexerHandlerFactory $indexerHandlerFactory,
                                ConfigInterface       $config,
                                FullFactory           $fullActionFactory,
                                PsrLoggerInterface    $logger,
                                Logger                $doofinderlogger
    )
    {
        $this->registration = $registration;
        $this->storeConfig = $storeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->productFactory = $productFactory;
        $this->indexerScope = $indexerScope;
        $this->indexerHelper = $indexerHelper;
        $this->fulltextResource = $fulltextResource;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->config = $config;
        $this->fullActionFactory = $fullActionFactory;
        $this->logger = $logger;
        $this->doofinderLogger = $doofinderlogger;

    }

    /**
     * @param BasePriceStorage $basePriceStorage
     * @param $result
     * @param array $prices
     * @return mixed
     * @throws Exception
     */
    public function afterUpdate(BasePriceStorage $basePriceStorage, $result, array $prices)
    {
        if ($this->storeConfig->isDoofinderFeedConfigured()) {
            $entityIds = array();
            $productModel = $this->productFactory->create();
            $stores = $this->storeConfig->getAllStores();
            $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
            foreach ($stores as $store) {
                if ($this->storeConfig->isUpdateByApiEnable($store->getCode())) {
                    foreach ($prices as $price) {
                        try {
                            $sku = $price->getSku();
                            if (!isset($entityIds[$sku])) {
                                $entityIds[$sku] = $productModel->getIdBySku($sku);
                            }
                        } catch (Exception $e) {
                            // log exception
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'Price'], 'Location' => ['function' => 'afterUpdate', 'product' => ['products' => $entityIds, 'storecode' => $store->getCode()], 'exception' => ['message' => $e->getMessage(), 'stacktrace' => $e->getTraceAsString()]]));
                            continue;
                        }
                    }

                    if ($indexer->isScheduled()) {
                        foreach ($entityIds as $id) {
                            $this->registration->registerUpdate($id, $store->getCode());
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'Price', 'Mode' => 'onSchedule'], 'Location' => ['function' => 'afterUpdate', 'product' => ['productid' => $id, 'storecode' => $store->getCode()]]));
                        }
                    } else {
                        try {
                            $data = $this->config->getIndexers()['catalogsearch_fulltext'];

                            $fullAction = $this->indexerHelper->createFullAction($data, $this->fullActionFactory);

                            $indexerHandler = $this->indexerHelper->createDoofinderIndexerHandler($data, $this->indexerHandlerFactory);

                            $dimensions = array($this->indexerHelper->getDimensions($store->getId()));

                            $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);

                            $productIds = array_unique(array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds)));

                            $indexerHandler->saveIndex($dimensions, $fullAction->rebuildStoreIndex($store->getId(), $productIds));
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'Price', 'Mode' => 'onSave'], 'Location' => ['function' => 'afterUpdate', 'product' => ['productid' => $productIds, 'storecode' => $store->getCode()]]));

                        } catch (Exception $e) {
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'Price'], 'Location' => ['function' => 'afterUpdate', 'product' => ['productid' => $productIds, 'storecode' => $store->getCode()], 'exception' => ['message' => $e->getMessage(), 'stacktrace' => $e->getTraceAsString()]]));
                            throw $e;
                        } finally {
                            $this->indexerScope->setIndexerScope(null);
                        }
                    }
                }
            }
        }
        return $result;
    }
}
