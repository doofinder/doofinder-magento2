<?php

namespace Doofinder\Feed\Observer\DelayedUpdates;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\DelayedUpdates as Helper;
use Doofinder\Feed\Model\ChangedProductFactory;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Model\ResourceModel\ChangedProductFactory as ChangedProductResourceFactory;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\CollectionFactory as ChangedProductCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for leaving a trace of a change of a product to be later synchronized using cron update.
 *
 * Execution of this plugin results in storing following
 * information in database table (name of the table is stored as a `TABLE_NAME` constant
 * in `Doofinder\Feed\Model\ResourceModel\ChangedProduct` class):
 *
 * - product's ID,
 * - type of operation (further described in `Doofinder\Feed\Model\ResourceModel\ChangedProduct` class),
 * - store view code (or null for every store view).
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class RegisterChange implements ObserverInterface
{
    /**
     * @var StoreConfig $storeConfig
     */
    private $storeConfig;

    /**
     * @var ChangedProductFactory $changedProductFactory
     */
    private $changedProductFactory;

    /**
     * @var ChangedProductFactory $changedProductResourceFactory
     */
    private $changedProductResourceFactory;

    /**
     * @var ChangedProductCollectionFactory $changedProductCollectionFactory
     */
    private $changedProductCollectionFactory;

    /**
     * @var Helper $helper
     */
    private $helper;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * A constructor.
     *
     * @param StoreConfig $storeConfig
     * @param ChangedProductFactory $changedProductFactory
     * @param ChangedProductResourceFactory $changedProductResourceFactory
     * @param ChangedProductCollectionFactory $changedProductCollectionFactory
     * @param Helper $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreConfig $storeConfig,
        ChangedProductFactory $changedProductFactory,
        ChangedProductResourceFactory $changedProductResourceFactory,
        ChangedProductCollectionFactory $changedProductCollectionFactory,
        Helper $helper,
        LoggerInterface $logger
    ) {
        $this->storeConfig = $storeConfig;
        $this->changedProductFactory = $changedProductFactory;
        $this->changedProductResourceFactory = $changedProductResourceFactory;
        $this->changedProductCollectionFactory = $changedProductCollectionFactory;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Stores a trace of product change. The trace is later picked up
     * by delayed update and processed.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Product $product
         */
        $product = $this->getAlteredProduct($observer);

        /**
         * @var string $operation
         */
        $operation = $this->getOperation($observer);

        /**
         * @var string[] $relevantStoreCodes
         */
        $relevantStoreCodes = $this->getRelevantStoreCodes();

        /**
         * This invocation resolves to running one of three methods:
         *
         * `leaveUpdateTrace()` for update operation, when a product's data
         * has been altered,
         *
         * `leaveDisableTrace()` for disable operation, when a product still
         * exists but is disabled in specific store view,
         *
         * `leaveDeleteTrace()` when a product was deleted from the system.
         *
         * Each of this method then calls a `leaveTrace()` method with proper
         * operation as an argument, and only this method eventually stores
         * the trace.
         */
        $this->leaveChangeTrace(
            $product,
            $operation,
            $relevantStoreCodes
        );
    }

    /**
     * Retrieves a product whose data has been changed.
     *
     * @param Observer $observer
     *
     * @return Product
     */
    private function getAlteredProduct(Observer $observer)
    {
        return $observer->getProduct();
    }

    /**
     * Determines the operation that was performed on a product.
     *
     * @param Observer $observer
     *
     * @return string A constant of `Doofinder\Feed\Model\ResourceModel\ChangedProduct`
     *  class indicating the operation associated
     */
    private function getOperation(Observer $observer)
    {
        $eventName = $observer->getEvent()
            ->getName();

        /**
         * Product that was deleted from all stores should be deleted from index as well.
         */
        if ($eventName == 'catalog_product_delete_commit_after') {
            return ChangedProductResource::OPERATION_DELETE;
        }

        /**
         * When there is no need to remove a product form the index, an update
         * should be issued instead.
         */
        return ChangedProductResource::OPERATION_UPDATE;
    }

    /**
     * Determines what store views are affected by the product change.
     *
     * An array with a single store view code is returned, when there is only
     * a single store view in the system, or the change was made on a specific
     * store view.
     *
     * An array with all store views' codes is returned when the change
     * was performed on a default store view (top level config).
     *
     * @return string[] An array of codes of store views that are affected
     *  by the product change.
     */
    private function getRelevantStoreCodes()
    {
        // Set relevantStoreCodes array with code of current store view, it can be specific store or admin for default
        $relevantStoreCodes = [$this->storeConfig->getCurrentStoreCode()];

        if ($relevantStoreCodes[0] === 'admin') {
            // if current store view is admin, then substitute $relevantStoreCodes with codes of all store views
            $relevantStoreCodes = $this->storeConfig->getStoreCodes();
        }

        return $relevantStoreCodes;
    }

    /**
     * Determines, what trace (either update or delete) should be recorded
     * for the product change, and invokes the method responsible for the appropriate
     * one among them on every relevant store view.
     *
     * @param Product $product
     * @param string $operation
     * @param string[] $relevantStoreCodes
     *
     * @return void
     */
    private function leaveChangeTrace(
        Product $product,
        $operation,
        array $relevantStoreCodes
    ) {
        foreach ($relevantStoreCodes as $storeCode) {
            switch ($operation) {
                case ChangedProductResource::OPERATION_UPDATE:
                    $this->leaveUpdateTrace($product, $storeCode);
                    break;

                case ChangedProductResource::OPERATION_DELETE:
                    $this->leaveDeleteTrace($product, $storeCode);
                    break;

                default:
                    $this->logger
                        ->warning(
                            'Unknown operation type "'
                            . $operation
                            . '" encountered while registering a change of product with ID '
                            . $product->getId()
                            . ' for delayed update.'
                        );
            }
        }
    }

    /**
     * Leaves an update trace for a product change.
     *
     * This method invokes a `leaveTrace()` method with appropriate
     * operation value as its parameter.
     *
     * @param Product $product
     * @param string $storeCode
     *
     * @return void
     */
    private function leaveUpdateTrace(
        Product $product,
        $storeCode
    ) {
        $this->leaveTrace(
            $product,
            $storeCode,
            ChangedProductResource::OPERATION_UPDATE
        );
    }

    /**
     * Leaves a delete trace for a product change.
     *
     * This method invokes a `leaveTrace()` method with appropriate
     * operation value as its parameter.
     *
     * @param Product $product
     * @param string $storeCode
     *
     * @return void
     */
    private function leaveDeleteTrace(
        Product $product,
        $storeCode
    ) {
        $this->leaveTrace(
            $product,
            $storeCode,
            ChangedProductResource::OPERATION_DELETE
        );
    }

    /**
     * Stores a trace of a product change.
     *
     * If there already exists a trace of the product change in a given store view,
     * then it's retrieved and updated with an appropriate operation value.
     *
     * If there is no trace for the product in given store view, a new one is created.
     *
     * @param Product $product
     * @param string $storeCode
     * @param string $operation
     *
     * @return void
     */
    private function leaveTrace(
        Product $product,
        $storeCode,
        $operation
    ) {
        /**
         * If there is already a product change trace for the product on given store view
         * recorded for `Doofinder\Feed\Cron\PerformDelayedUpdates` cron job to pick up
         * and process, only the information of the operation to be performed can be modified.
         */
        $changedProductCollection = $this->changedProductCollectionFactory
            ->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                ChangedProductResource::FIELD_PRODUCT_ID,
                $product->getId()
            )
            ->addFieldToFilter(
                ChangedProductResource::FIELD_STORE_CODE,
                $storeCode
            );

        /**
         * If there is no trace of product change stored in database, the collection
         * will be empty, but `getFirstItem()` creates a fresh trace instance in this case.
         *
         * Lints for `getFirstItem()` are disabled, because collection is set to return
         * only a single result through `setPageSize()` method.
         */
        // phpcs:disable MEQP1.Performance.InefficientMethods.FoundGetFirstItem
        // phpcs:disable Ecg.Performance.GetFirstItem.Found
        $trace = $changedProductCollection
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
        // phpcs:enable

        /**
         * In case there already exists a trace of product change, setting
         * the product ID and store code on it again has no effect (they're being
         * overwritten with the same information).
         *
         * Otherwise, all informations are set on a new trace.
         */
        $trace->setProductEntityId($product->getId())
            ->setOperationType($operation)
            ->setStoreCode($storeCode);

        $this->changedProductResourceFactory
            ->create()
            ->save($trace);
    }
}
