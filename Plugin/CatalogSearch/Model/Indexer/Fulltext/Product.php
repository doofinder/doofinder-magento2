<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer\Fulltext;

use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreDimensionProvider;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Model\ChangedProduct\Registration;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * Catalog search indexer plugin for catalog product used to register product
 * updates when catalogsearch index update mode is set to "on schedule".
 */
class Product extends AbstractPlugin
{
    /**
     * @var Registration
     */
    private $registration;

    /**
     * @var StoreDimensionProvider
     */
    private $storeDimensionProvider;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @param Registration $registration
     * @param StoreDimensionProvider $storeDimensionProvider
     * @param StoreConfig $storeConfig
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Registration $registration,
        StoreDimensionProvider $storeDimensionProvider,
        StoreConfig $storeConfig,
        IndexerRegistry $indexerRegistry
    ) {
        $this->registration = $registration;
        $this->storeDimensionProvider = $storeDimensionProvider;
        $this->storeConfig = $storeConfig;
        $this->indexerRegistry = $indexerRegistry;
    }
    /**
     * Reindex on product save.
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function afterSave(ResourceProduct $productResource, $result, AbstractModel $product)
    {
        $stores = $this->storeConfig->getAllStores();
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
        foreach($stores as $store) {
            if ($this->storeConfig->isUpdateByApiEnable($store->getCode()) && $indexer->isScheduled()) {
                $this->registration->registerUpdate(
                    $product->getId(), 
                    $store->getCode()
                );
            }
        }
        return $result;
    }

    /**
     * Reindex on product delete
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function afterDelete(ResourceProduct $productResource, $result, AbstractModel $product)
    {
        $stores = $this->storeConfig->getAllStores();
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
        foreach($stores as $store) {
            if ($this->storeConfig->isUpdateByApiEnable($store->getCode()) && $indexer->isScheduled()) {
                $this->registration->registerDelete(
                    $product->getId(), 
                    $store->getCode()
                );
            }
        }
        return $result;
    }
}
