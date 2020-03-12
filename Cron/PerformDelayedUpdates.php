<?php

namespace Doofinder\Feed\Cron;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\CollectionFactory as ChangedProductCollectionFactory;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection as ChangedProductCollection;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Model\Indexer\IndexerHandler;
use Doofinder\Feed\Registry\IndexerScope;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Area;

/**
 * This class reflects current product data in Doofinder on cron run.
 *
 */
class PerformDelayedUpdates
{
    /**
     * @var StoreConfig $storeConfig
     */
    private $storeConfig;

    /**
     * @var ChangedProductCollectionFactory $changedColFactory
     */
    private $changedColFactory;

    /**
     * @var IndexerHelper
     */
    private $indexerHelper;

    /**
     * @var IndexerHandler
     */
    private $indexerHandler;

    /**
     * @var IndexerScope
     */
    private $indexerScope;

    /**
     * @var Full
     */
    private $fullAction;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * PerformDelayedUpdates constructor.
     * @param StoreConfig $storeConfig
     * @param ChangedProductCollectionFactory $changedColFactory
     * @param IndexerHelper $indexerHelper
     * @param IndexerHandler $indexerHandler
     * @param IndexerScope $indexerScope
     * @param Full $fullAction
     * @param Emulation $appEmulation
     */
    public function __construct(
        StoreConfig $storeConfig,
        ChangedProductCollectionFactory $changedColFactory,
        IndexerHelper $indexerHelper,
        IndexerHandler $indexerHandler,
        IndexerScope $indexerScope,
        Full $fullAction,
        Emulation $appEmulation
    ) {
        $this->storeConfig = $storeConfig;
        $this->changedColFactory = $changedColFactory;
        $this->indexerHelper = $indexerHelper;
        $this->indexerHandler = $indexerHandler;
        $this->indexerScope = $indexerScope;
        $this->fullAction = $fullAction;
        $this->appEmulation = $appEmulation;
    }

    /**
     * Processes all product change traces for each store view.
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->indexerHelper->isDelayedUpdatesEnabled()) {
            return;
        }

        $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_DELAYED);
        foreach ($this->storeConfig->getStoreCodes() as $storeCode) {
            $storeId = $this->storeConfig->getStoreViewIdByStoreCode($storeCode);
            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

            $dimensions = $this->getDimensions($storeCode);
            $this->indexerHandler->deleteIndex(
                $dimensions,
                $this->getDeletedDocuments($storeCode)
            );
            $this->indexerHandler->saveIndex(
                $dimensions,
                $this->getUpdatedDocuments($storeCode)
            );

            $this->appEmulation->stopEnvironmentEmulation();
        }
        $this->indexerScope->setIndexerScope(null);
    }

    /**
     * @param string $storeCode
     * @return array
     */
    private function getDimensions($storeCode)
    {
        $storeId = $this->storeConfig->getStoreViewIdByStoreCode($storeCode);
        return [$this->indexerHelper->getDimensions($storeId)];
    }

    /**
     * Processes all deleted products traces for given store view.
     *
     * @param string $storeCode Code of the store view traces should be processed on.
     *
     * @return void|\Generator
     */
    private function getDeletedDocuments($storeCode)
    {
        /**
         * @var ChangedProductCollection $collection
         */
        $collection = $this->getDeletedProductsCollection($storeCode);

        if ($collection->getSize() === 0) {
            return;
        }

        foreach ($collection as $item) {
            yield $item[ChangedProductResource::FIELD_PRODUCT_ID];
        }

        $collection->walk('delete');
    }

    /**
     * Processes all updated products traces for given store view.
     *
     *
     * @param string $storeCode Code of the store view traces should be processed on.
     *
     * @return void|\Generator
     */
    private function getUpdatedDocuments($storeCode)
    {
        /**
         * @var ChangedProductCollection $collection
         */
        $collection = $this->getUpdatedProductsCollection($storeCode);

        $productIds = $collection->getColumnValues(ChangedProductResource::FIELD_PRODUCT_ID);
        $collection->walk('delete');

        $storeId = $this->storeConfig->getStoreViewIdByStoreCode($storeCode);
        return $this->fullAction->rebuildStoreIndex($storeId, $productIds);
    }

    /**
     * Returns a collection of product traces for products that have been deleted.
     *
     * @param string $storeCode Code of the store product delete or disable traces should be returned for.
     *
     * @return ChangedProductCollection
     */
    private function getDeletedProductsCollection($storeCode)
    {
        return $this->changedColFactory->create()->filterDeleted($storeCode);
    }

    /**
     * Returns a collection of product traces for products that have been updated.
     * @param string $storeCode Code of the store product update traces should be returned for.
     *
     * @return ChangedProductCollection
     */
    private function getUpdatedProductsCollection($storeCode)
    {
        return $this->changedColFactory->create()->filterUpdated($storeCode);
    }
}
