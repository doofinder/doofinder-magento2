<?php

namespace Doofinder\Feed\Cron;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\CollectionFactory as ChangedProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Model\Indexer\IndexerHandler;
use Doofinder\Feed\Registry\IndexerScope;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;

/**
 * This class reflects current product data in Doofinder on cron run.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class PerformDelayedUpdates
{
    /**
     * @var StoreConfig $storeConfig
     */
    private $storeConfig;

    /**
     * @var ChangedProductCollectionFactory $changedProductCollectionFactory
     */
    private $changedProductCollectionFactory;

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
     * PerformDelayedUpdates constructor.
     * @param StoreConfig $storeConfig
     * @param ChangedProductCollectionFactory $changedProductCollectionFactory
     * @param IndexerHelper $indexerHelper
     * @param IndexerHandler $indexerHandler
     * @param IndexerScope $indexerScope
     * @param Full $fullAction
     */
    public function __construct(
        StoreConfig $storeConfig,
        ChangedProductCollectionFactory $changedProductCollectionFactory,
        IndexerHelper $indexerHelper,
        IndexerHandler $indexerHandler,
        IndexerScope $indexerScope,
        Full $fullAction
    ) {
        $this->storeConfig = $storeConfig;
        $this->changedProductCollectionFactory = $changedProductCollectionFactory;
        $this->indexerHelper = $indexerHelper;
        $this->indexerHandler = $indexerHandler;
        $this->indexerScope = $indexerScope;
        $this->fullAction = $fullAction;
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
            $dimensions = $this->getDimensions($storeCode);

            $this->indexerHandler->deleteIndex(
                $dimensions,
                $this->getDeletedDocuments($storeCode)
            );
            $this->indexerHandler->saveIndex(
                $dimensions,
                $this->getUpdatedDocuments($storeCode)
            );
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
         * @var \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection $collection
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
         * @var \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection $collection
         */
        $collection = $this->getUpdatedProductsCollection($storeCode);

        $productIds = $collection->getColumnValues(ChangedProductResource::FIELD_PRODUCT_ID);
        $collection->walk('delete');

        return $this->fullAction->rebuildStoreIndex($storeCode, $productIds);
    }

    /**
     * Returns a collection of products change traces.
     *
     * This method fetches at most 100 traces per request, and processes all of the traces
     * in subsequent API calls.
     *
     * @param string $type      Type of operation executed on a product. This can be either 'update' or 'delete'.
     *                          Using OPERATION constats of class Doofinder\Feed\Model\ResourceModel\ChangedProduct
     *                          is strongly advised.
     * @param string $storeCode Code of store view which product change traces should be returned for.
     *
     * @return \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection A collection of products change traces.
     */
    private function getChangedProductCollection(
        $type,
        $storeCode
    ) {
        return $this->changedProductCollectionFactory
            ->create()
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect(ChangedProductResource::FIELD_PRODUCT_ID)
            ->addFieldToFilter(
                ChangedProductResource::FIELD_OPERATION_TYPE,
                $type
            )
            ->addFieldToFilter(
                ChangedProductResource::FIELD_STORE_CODE,
                $storeCode
            )
            ->load();
    }

    /**
     * Returns a collection of product traces for products that have been deleted.
     *
     * @param string $storeCode Code of the store product delete or disable traces should be returned for.
     *
     * @return \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection
     */
    private function getDeletedProductsCollection($storeCode)
    {
        return $this->getChangedProductCollection(
            [
                ChangedProductResource::OPERATION_DELETE,
            ],
            $storeCode
        );
    }

    /**
     * Returns a collection of product traces for products that have been updated.
     *
     * @param string $storeCode Code of the store product update traces should be returned for.
     *
     * @return \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection
     */
    private function getUpdatedProductsCollection($storeCode)
    {
        return $this->getChangedProductCollection(
            ChangedProductResource::OPERATION_UPDATE,
            $storeCode
        );
    }
}
