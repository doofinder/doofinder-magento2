<?php

namespace Doofinder\Feed\Cron;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\CollectionFactory as ChangedProductCollectionFactory;
use Doofinder\Feed\Search\Processor;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * This class reflects current product data in Doofinder on cron run.
 *
 * When 'Update on cron' option is selected in admin panel and Doofinder
 * is set as internal search engine, product updates and deletions are postponed
 * to next cron run instead of being executed immediately. This class then
 * executes those operations in bulk.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class PerformDelayedUpdates
{
    /**
     * Determines how much products at most are to be processed in a single API call.
     *
     * @var integer CHUNK_SIZE
     */
    const CHUNK_SIZE = 100;

    /**
     * @var StoreConfig $storeConfig
     */
    private $storeConfig;

    /**
     * @var ChangedProductCollectionFactory $changedProductCollectionFactory
     */
    private $changedProductCollectionFactory;

    /**
     * @var Processor $processor
     */
    private $processor;

    /**
     * @var ProductCollectionFactory $productCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * A constructor.
     *
     * @param StoreConfig $storeConfig
     * @param ChangedProductCollectionFactory $changedProductCollectionFactory
     * @param Processor $processor
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        StoreConfig $storeConfig,
        ChangedProductCollectionFactory $changedProductCollectionFactory,
        Processor $processor,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->storeConfig = $storeConfig;
        $this->changedProductCollectionFactory = $changedProductCollectionFactory;
        $this->processor = $processor;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Processes all product change traces for each store view.
     *
     * @return void
     */
    public function execute()
    {
        foreach ($this->storeConfig->getStoreCodes() as $storeCode) {
            if (!$this->storeConfig->isDelayedUpdatesEnabled($storeCode)) {
                continue;
            }

            $this->processDeletedDocuments($storeCode);
            $this->processUpdatedDocuments($storeCode);
        }
    }

    /**
     * Processes all deleted products traces for given store view.
     *
     * @param string $storeCode Code of the store view traces should be processed on.
     *
     * @return void
     */
    private function processDeletedDocuments($storeCode)
    {
        do {
            /**
             * @var \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection $collection
             */
            $collection = $this->getDeletedProductsCollection($storeCode);

            if ($collection->getSize() === 0) {
                return;
            }

            /**
             * We only need to know the IDs of deleted products and pass them to the processor.
             */
            $deletedIds = $collection->getColumnValues(ChangedProductResource::FIELD_PRODUCT_ID);

            // phpcs:ignore Ecg.Performance.Loop.ModelLSD, MEQP1.Performance.Loop.ModelLSD
            $this->processor->delete($storeCode, $deletedIds);

            $collection->walk('delete');
        } while ($collection->getSize() == self::CHUNK_SIZE);
    }

    /**
     * Processes all updated products traces for given store view.
     *
     * This method fetches at most 100 traces per request, and processes all of the traces
     * in subsequent API calls.
     *
     * @param string $storeCode Code of the store view traces should be processed on.
     *
     * @return void
     */
    private function processUpdatedDocuments($storeCode)
    {
        do {
            /**
             * @var \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection $collection
             */
            $collection = $this->getUpdatedProductsCollection($storeCode);

            if ($collection->getSize() === 0) {
                return;
            }

            /**
             * We only need to know the IDs of updated products and pass them
             * to the collection factory.
             */
            $updatedIds = $collection->getColumnValues(ChangedProductResource::FIELD_PRODUCT_ID);

            $storeViewId = $this->storeConfig
                ->getStoreViewIdByStoreCode($storeCode);

            $productCollection = $this->productCollectionFactory
                ->create()
                ->addStoreFilter($storeViewId)
                ->addFieldToSelect('*')
                ->addFieldToFilter(
                    'entity_id',
                    [
                        'in' => $updatedIds
                    ]
                );

            /**
             * The `update` method of `Doofinder\Feed\Search\Processor` class expects
             * an array of products' models.
             */
            $updatedProducts = [];
            foreach ($productCollection as $product) {
                $updatedProducts[] = $product;
            }

            $this->processor->update($storeCode, $updatedProducts);

            $collection->walk('delete');
        } while ($collection->getSize() == self::CHUNK_SIZE);
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
            ->setPageSize(self::CHUNK_SIZE)
            ->setCurPage(1)
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
                ChangedProductResource::OPERATION_DISABLE
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
