<?php

namespace Doofinder\Feed\Model\Indexer;

/**
 * Search processor
 */
class Processor
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $searchHelper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\Search $searchHelper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\Search $searchHelper
    ) {
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->searchHelper = $searchHelper;
    }

    /**
     * Update index items
     *
     * @param  string $store
     * @param  Product[] $products
     * @return void
     */
    public function update($store, array $products)
    {
        if (empty($products)) {
            return;
        }

        // reset indexes
        $products = array_values($products);

        $this->performInStore($store, function () use ($store, $products) {
            $this->performUpdate($products);
        });
    }

    /**
     * Delete index items
     *
     * @param  string $store
     * @param  integer[] $productIds
     * @return void
     */
    public function delete($store, array $productIds)
    {
        if (empty($productIds)) {
            return;
        }

        // reset indexes
        $productIds = array_values($productIds);

        $this->performInStore($store, function () use ($store, $productIds) {
            $this->performDelete($productIds);
        });
    }

    /**
     * Perform in store
     *
     * @param  string $storeId
     * @param  \Closure $closure
     * @return void
     */
    private function performInStore($storeId, \Closure $closure)
    {
        $originalStoreCode = $this->storeConfig->getStoreCode();

        $this->storeManager->setCurrentStore($storeId);

        // Call given closure
        $closure();

        $this->storeManager->setCurrentStore($originalStoreCode);
    }

    /**
     * Update products in Doofinder index
     *
     * @param  Product[] $products
     * @return void
     */
    private function performUpdate(array $products)
    {
        $this->searchHelper->updateDoofinderItems($products);
    }

    /**
     * Delete products in Doofinder index
     *
     * @param  array $productIds
     * @return void
     */
    private function performDelete(array $productIds)
    {
        $this->searchHelper->deleteDoofinderItems($productIds);
    }
}
