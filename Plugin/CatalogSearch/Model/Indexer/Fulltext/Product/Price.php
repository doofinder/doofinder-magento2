<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer\Fulltext\Product;

use Magento\Catalog\Model\Product\Price\BasePriceStorage;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Model\AbstractModel;
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


/**
 * Catalog search indexer plugin for catalog product used to register product
 * updates when catalogsearch index update mode is set to "on schedule".
 */
class Price extends AbstractPlugin
{
    /**
     * @var Registration
     */
    private $registration;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

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
     * @var ConfigInterface
     */
    protected $config;

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
    public function __construct(
        Registration $registration,
        StoreConfig $storeConfig,
        IndexerRegistry $indexerRegistry,
        ProductFactory $productFactory,
        IndexerScope $indexerScope,
        IndexerHelper $indexerHelper,
        FulltextResource $fulltextResource,
        IndexerHandlerFactory $indexerHandlerFactory,
        ConfigInterface $config,
        FullFactory $fullActionFactory,
        PsrLoggerInterface $logger
    ) {
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

    }
    /**
     * @param ItemResourceModel $subject
     * @param callable $proceed
     * @param AbstractModel $legacyStockItem
     * @return ItemResourceModel
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdate(BasePriceStorage $basePriceStorage, $result, array $prices)
    {
        $entityIds = array();
        $productModel = $this->productFactory->create();
        $stores = $this->storeConfig->getAllStores();
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
        $data = $this->config->getIndexers()['catalogsearch_fulltext'];
        try {
            $indexerHandler = $this->createDoofinderIndexerHandler($data);
        } catch (\LogicException $e) {
            return $result;
        }
        $indexerHandler = $this->createDoofinderIndexerHandler($data);
        $fullAction = $this->createFullAction($data);
        foreach($stores as $store) {
            if ($this->storeConfig->isUpdateByApiEnable($store->getCode())) {
                foreach ($prices as $price) {
                    try {
                        $sku = $price->getSku();
                        if(!isset($entityIds[$sku])) {
                            $entityIds[$sku] = $productModel->getIdBySku($sku);
                        }
                    } catch (\Exception $e) {
                        // TODO: log exception
                        $this->logger->error($e->getMessage());
                        continue;
                    }
                }
            } 

            if ($indexer->isScheduled()) {
                foreach ($entityIds as $id) {
                    
                    $this->registration->registerDelete(
                        $id,
                        $store->getCode()
                        );
                    $this->registration->registerUpdate(
                        $id, 
                        $store->getCode()
                    );
                }
            } else {
                try {
                    $dimensions = array($this->indexerHelper->getDimensions($store->getId()));
                    $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                    $productIds = array_unique(
                        array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds))
                    );
                    $indexerHandler->deleteIndex(
                        $dimensions,
                        new \ArrayIterator($productIds)
                    );
                    $indexerHandler->saveIndex(
                        $dimensions,
                        $fullAction->rebuildStoreIndex($store->getId(), $productIds)
                    );
                } catch(\Exception $e) {
                    throw $e;
                } finally {
                    $this->indexerScope->setIndexerScope(null);
                }
            }
        }
        return $result;
    }

    private function createDoofinderIndexerHandler(array $data = []) {
        return $this->indexerHandlerFactory->create($data);
    }

    private function createFullAction(array $data) {
        return $this->fullActionFactory->create(['data' => $data]);
    }
}
