<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Model\Indexer\Data\Map\Update\FetcherInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class Doofinder
 * The class responsible for providing custom Doofinder attributes to index
 */
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
     * @var CollectionFactory
     */
    private $productColFactory;

    /**
     * @var Status
     */
    private $stockStatusResource;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * Doofinder constructor.
     * @param CollectionFactory $collectionFactory
     * @param Status $stockStatusResource
     * @param StoreConfig $storeConfig
     * @param array $generators
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Status $stockStatusResource,
        StoreConfig $storeConfig,
        array $generators
    ) {
        $this->productColFactory = $collectionFactory;
        $this->stockStatusResource = $stockStatusResource;
        $this->storeConfig = $storeConfig;
        $this->generators = $generators;
    }

    /**
     * {@inheritDoc}
     * @param array $documents
     * @param integer $storeId
     * @return void
     */
    public function process(array $documents, $storeId)
    {
        $this->clear();
        $productIds = array_keys($documents);

        $productCol = $this->getProductCollection($productIds, $storeId);

        $fields = $this->getFields($storeId);
        foreach ($productCol as $product) {
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
     * {@inheritDoc}
     * @param integer $productId
     * @return array
     */
    public function get($productId)
    {
        return $this->processed[$productId] ?? [];
    }

    /**
     * {@inheritDoc}
     * @return void
     */
    public function clear()
    {
        $this->processed = [];
    }

    /**
     * Get product generator
     * @param string $type
     * @return mixed
     */
    private function getGenerator($type)
    {
        return isset($this->generators[$type]) ?
            $this->generators[$type] : $this->generators['simple'];
    }

    /**
     * Get Doofinder fields configured in specific store view
     * @param integer $storeId
     * @return array
     */
    private function getFields($storeId)
    {
        return $this->storeConfig->getDoofinderFields($storeId);
    }

    /**
     * @param array $productIds
     * @param integer $storeId
     * @return Collection
     */
    private function getProductCollection(array $productIds, $storeId)
    {
        $attributes = array_values($this->getFields($storeId));
        $collection = $this->productColFactory->create()
            ->addIdFilter($productIds)
            ->addAttributeToSelect($attributes)
            ->addStoreFilter($storeId)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
            ])
            ->addAttributeToSort('id', 'asc');

        /**
         * @notice Magento 2.2.x included a default stock filter
         *         so that 'out of stock' products are excluded by default.
         *         We override this behavior here.
         */
        $collection->setFlag('has_stock_status_filter', true);
        $this->stockStatusResource->addStockDataToCollection($collection, false);

        return $collection;
    }
}
