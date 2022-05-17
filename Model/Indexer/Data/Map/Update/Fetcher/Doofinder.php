<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Api\Data\FetcherInterface;
use Doofinder\Feed\Api\Data\Generator\MapInterface;
use Doofinder\Feed\Model\Config\Indexer\Attributes;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

class Doofinder implements FetcherInterface
{
    /**
     * @var array|null
     */
    private $processed;

    /**
     * @var array
     */
    private $generators;

    /**
     * @var ProductCollectionFactory
     */
    private $productColFactory;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var AddStockDataToCollection
     */
    private $addStockDataToCollection;

    /**
     * @var Attributes
     */
    private $attributes;

    /**
     * Doofinder constructor.
     *
     * @param ProductCollectionFactory $collectionFactory
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param AddStockDataToCollection $addStockDataToCollection
     * @param Attributes $attributes
     * @param array $generators
     */
    public function __construct(
        ProductCollectionFactory $collectionFactory,
        DefaultStockProviderInterface $defaultStockProvider,
        AddStockDataToCollection $addStockDataToCollection,
        Attributes $attributes,
        array $generators
    ) {
        $this->productColFactory = $collectionFactory;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->addStockDataToCollection = $addStockDataToCollection;
        $this->attributes = $attributes;
        $this->generators = $generators;
    }

    /**
     * @inheritDoc
     */
    public function process(array $documents, int $storeId)
    {
        $this->clear();
        $productIds = array_keys($documents);
        $productCollection = $this->getProductCollection($productIds, $storeId);
        $fields = $this->getFields($storeId);
        foreach ($productCollection as $product) {
            $productId = $product->getId();
            $type = strtolower($product->getTypeId());
            $generator = $this->getGenerator($type);
            $this->processed[$productId] = [];
            foreach ($fields as $indexField => $attribute) {
                $this->processed[$productId][$indexField] = $generator->get($product, $attribute);
            }
            $this->processed[$productId] = array_filter($this->processed[$productId]);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(int $productId): array
    {
        return $this->processed[$productId] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->processed = [];
    }

    /**
     * Get product generator
     *
     * @param string $type
     * @return MapInterface
     */
    private function getGenerator(string $type): MapInterface
    {
        return $this->generators[$type] ?? $this->generators['simple'];
    }

    /**
     * Get Doofinder fields configured in specific store view
     * @param integer $storeId
     * @return array
     */
    private function getFields(int $storeId): array
    {
        return $this->attributes->get($storeId);
    }

    /**
     * @param array $productIds
     * @param integer $storeId
     * @param int|null $stockId
     *
     * @return ProductCollection
     */
    private function getProductCollection(array $productIds, int $storeId, ?int $stockId = null): ProductCollection
    {
        $collection = $this->productColFactory
            ->create()
            ->addIdFilter($productIds)
            ->addAttributeToSelect('*')
            ->addStoreFilter($storeId)
            ->addAttributeToSort('id', 'asc');
        /**
         * @notice Magento 2.2.x included a default stock filter
         *         so that 'out of stock' products are excluded by default.
         *         We override this behavior here.
         */
        $collection->setFlag('has_stock_status_filter', true);
        $stockId = $stockId ?? $this->defaultStockProvider->getId();
        $this->addStockDataToCollection->execute($collection, false, $stockId);

        return $collection;
    }
}
