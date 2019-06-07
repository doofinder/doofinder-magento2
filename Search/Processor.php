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
     * @var \Doofinder\Feed\Registry\IndexerScope
     */
    private $indexerScope;

    /**
     * Store performed products in array to prevent run indexer more than one time when saving product.
     * This is a workaround for Magento 2.3.1 where indexers are run four times.
     * @var array
     */
    private $performed;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     * @param \Doofinder\Feed\Helper\Search $searchHelper
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     * @param \Doofinder\Feed\Registry\IndexerScope $indexerScope
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Doofinder\Feed\Helper\Search $searchHelper,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Registry\IndexerScope $indexerScope
    ) {
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->feedConfig = $feedConfig;
        $this->searchHelper = $searchHelper;
        $this->generatorFactory = $generatorFactory;
        $this->indexerScope = $indexerScope;
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
        $isSaveScope = ($this->indexerScope->getIndexerScope() === \Doofinder\Feed\Registry\IndexerScope::SCOPE_SAVE);
        if ($isSaveScope) {
            foreach ($products as $key => $product) {
                if ($this->hasBeenPerformed('update', $store, $product)) {
                    unset($products[$key]);
                }
            }
        }

        if (empty($products)) {
            return;
        }

        $this->performInStore($store, function () use ($store, $products, $isSaveScope) {
            $this->performUpdate($products);
            if ($isSaveScope) {
                $this->setHasBeenPerformed('update', $store, $products);
            }
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
        $isSaveScope = ($this->indexerScope->getIndexerScope() === \Doofinder\Feed\Registry\IndexerScope::SCOPE_SAVE);
        if ($isSaveScope) {
            foreach ($productIds as $key => $product) {
                if ($this->hasBeenPerformed('delete', $store, $product)) {
                    unset($productIds[$key]);
                }
            }
        }

        if (empty($productIds)) {
            return;
        }

        $this->performInStore($store, function () use ($store, $productIds, $isSaveScope) {
            $this->performDelete($productIds);
            if ($isSaveScope) {
                $this->setHasBeenPerformed('delete', $store, $productIds);
            }
        });
    }

    /**
     * Check if product has been performed in store
     * @param string $type
     * @param integer $store
     * @param string|\Magento\Catalog\Model\Product $product
     * @return boolean
     */
    private function hasBeenPerformed($type, $store, $product)
    {
        if ($product instanceof \Magento\Catalog\Model\Product) {
            return isset($this->performed[$type][$store][$product->getId()]);
        }
        return isset($this->performed[$type][$store][$product]);
    }

    /**
     * Mark products as performed in store
     * @param string $type
     * @param integer $store
     * @param array|\Magento\Catalog\Model\Product[] $products
     * @return void
     */
    private function setHasBeenPerformed($type, $store, $products)
    {
        foreach ($products as $product) {
            if ($product instanceof \Magento\Catalog\Model\Product) {
                $this->performed[$type][$store][$product->getId()] = true;
                continue;
            }
            $this->performed[$type][$store][$product] = true;
        }
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
