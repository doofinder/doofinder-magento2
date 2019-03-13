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
class UpdateIndices
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
     * Executes job.
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->storeConfig->isCronUpdatesEnabled()) {
            return;
        }

        $this->processDocuments();
    }

    /**
     * Returns a collection of products change traces.
     *
     * @param string $type Type of operation executed on a product. This can be either 'update' or 'delete'.
     *  Using OPERATION constats of class Doofinder\Feed\Model\ResourceModel\ChangedProduct is strongly advised.
     *
     * @return \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection A collection of products change traces.
     */
    private function getChangedProductCollection($type)
    {
        return $this->changedProductCollectionFactory
            ->create()
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect(ChangedProductResource::FIELD_PRODUCT_ID)
            ->addFieldToFilter(
                ChangedProductResource::FIELD_OPERATION_TYPE,
                $type
            )
            ->setPageSize(self::CHUNK_SIZE)
            ->setCurPage(1)
            ->load();
    }

    /**
     * Returns a collection of product traces for products that have been deleted.
     *
     * @return \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection
     */
    private function getDeletedProductsCollection()
    {
        return $this->getChangedProductCollection(ChangedProductResource::OPERATION_DELETE);
    }

    /**
     * Returns a collection of product traces for products that have been updated.
     *
     * @return \Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection
     */
    private function getUpdatedProductsCollection()
    {
        return $this->getChangedProductCollection(ChangedProductResource::OPERATION_UPDATE);
    }

    /**
     * Processes all changed products traces.
     *
     * @return void
     */
    private function processDocuments()
    {
        $this->processDeletedDocuments();
        $this->processUpdatedDocuments();
    }

    /**
     * Processes all deleted products traces.
     *
     * @return void
     */
    private function processDeletedDocuments()
    {
        do {
            $collection = $this->getDeletedProductsCollection();

            if ($collection->getSize() === 0) {
                return;
            }

            $deletedIds = [];
            foreach ($collection as $item) {
                $deletedIds[] = $item->getProductEntityId();
            }

            foreach ($this->storeConfig->getStoreCodes() as $storeCode) {
                // phpcs:ignore Ecg.Performance.Loop.ModelLSD, MEQP1.Performance.Loop.ModelLSD
                $this->processor->delete($storeCode, $deletedIds);
            }

            $collection->walk('delete');
        } while ($collection->getSize() == self::CHUNK_SIZE);
    }

    /**
     * Processes all updated products traces.
     *
     * @return void
     */
    private function processUpdatedDocuments()
    {
        do {
            $collection = $this->getUpdatedProductsCollection();

            if ($collection->getSize() === 0) {
                return;
            }

            $updatedIds = [];
            foreach ($collection as $item) {
                $updatedIds[] = $item->getProductEntityId();
            }

            $productCollection = $this->productCollectionFactory
                ->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter(
                    'entity_id',
                    [
                        'in' => $updatedIds
                    ]
                );

            $updatedProducts = [];
            foreach ($productCollection as $product) {
                $updatedProducts[] = $product;
            }

            foreach ($this->storeConfig->getStoreCodes() as $storeCode) {
                $this->processor->update($storeCode, $updatedProducts);
            }

            $collection->walk('delete');
        } while ($collection->getSize() == self::CHUNK_SIZE);
    }
}
