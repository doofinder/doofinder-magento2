<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer\Fulltext;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ResourceStockItem;
use Magento\Framework\Model\AbstractModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Model\ChangedProduct\Registration;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Doofinder\Feed\Helper\Logger;


/**
 * Catalog search indexer plugin for catalog product used to register product
 * updates when catalogsearch index update mode is set to "on schedule".
 */
class StockItem extends AbstractPlugin
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;
    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;
    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaFactory;
    /**
     * @var Registration
     */
    private $registration;
    /**
     * @var StoreConfig
     */
    private $storeConfig;
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
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param Logger $doofinderLogger
     */
    public function __construct(
        Registration                      $registration,
        StoreConfig                       $storeConfig,
        IndexerRegistry                   $indexerRegistry,
        StockRegistryInterface            $stockRegistry,
        StockItemRepositoryInterface      $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        Logger                            $doofinderLogger
    )
    {
        $this->registration = $registration;
        $this->storeConfig = $storeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->doofinderLogger = $doofinderLogger;
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
    public function aroundSave(ResourceStockItem $subject, callable $proceed, AbstractModel $stockItem)
    {
        $result = $proceed($stockItem);
        if ($this->storeConfig->isDoofinderFeedConfigured()) {
            $origStockItem = $this->getOriginalStockItem($stockItem->getProductId());
            if ($this->registerUpdate($origStockItem, $stockItem)) {
                $stores = $this->storeConfig->getAllStores();
                $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
                foreach ($stores as $store) {
                    if ($this->storeConfig->isUpdateByApiEnable($store->getCode()) && $indexer->isScheduled()) {
                        $this->registration->registerUpdate(
                            $stockItem->getProductId(),
                            $store->getCode()
                        );
                        $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'StockItem', 'Mode' => 'onSchedule'], 'Location' => ['function' => 'aroundSave', 'product' => ['productid' => $stockItem->getProductId(), 'storecode' => $store->getCode()]]));
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $productId
     * @return false|mixed
     */
    private function getOriginalStockItem($productId)
    {
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter($productId);
        $collection = $this->stockItemRepository->getList($criteria);
        $stockItem = current($collection->getItems());
        return $stockItem;
    }

    /**
     * @param $origStockItem
     * @param $stockItem
     * @return bool
     */
    private function registerUpdate($origStockItem, $stockItem)
    {
        return ($origStockItem && $origStockItem->getIsInStock() != $stockItem->getIsInStock());
    }
}
