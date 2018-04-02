<?php

namespace Doofinder\Feed\Search;

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
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $searchHelper;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     * @param \Doofinder\Feed\Helper\Search $searchHelper
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Doofinder\Feed\Helper\Search $searchHelper,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
    ) {
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->feedConfig = $feedConfig;
        $this->searchHelper = $searchHelper;
        $this->generatorFactory = $generatorFactory;
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
        $this->performInStore($store, function () use ($products) {
            $this->performUpdate($products);
        });
    }

    /**
     * Delete index items
     *
     * @param  string $store
     * @param  int[] $productIds
     * @return void
     */
    public function delete($store, array $productIds)
    {
        $this->performInStore($store, function () use ($productIds) {
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
        $feedConfig = $this->feedConfig->getLeanFeedConfig();

        // Add fixed product fetcher
        $feedConfig['data']['config']['fetchers']['Product\Fixed'] = [
            'products' => $products,
        ];

        // Add atomic update processor
        $feedConfig['data']['config']['processors']['AtomicUpdater'] = [];

        $generator = $this->generatorFactory->create($feedConfig);
        $generator->run();
    }

    /**
     * Delete products in Doofinder index
     *
     * @param  int[] $ids
     * @return void
     */
    private function performDelete(array $ids)
    {
        $this->searchHelper->deleteDoofinderItems(array_map(function ($id) {
            return ['id' => $id];
        }, $ids));
    }
}
