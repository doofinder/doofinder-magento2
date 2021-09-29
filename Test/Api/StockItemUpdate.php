<?php

namespace Doofinder\Feed\Test\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedProduct\Processor\CollectionProvider;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Doofinder\FeedCompatibility\Test\Api\BaseWebapi;

use Doofinder\Feed\Test\Helper\Configuration as ConfigurationHelper;
use Doofinder\Feed\Test\Helper\Product as ProductHelper;

/**
 * Provide tests for the plugin.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class StockItemUpdate extends BaseWebapi
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var MutableScopeConfigInterface */
    private $mutableScopeConfig;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CollectionProvider */
    private $collectionProvider;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ConfigurationHelper */
    private $configHelper;

    /** @var ProductHelper */
    private $productHelper;
    
    /** * @var StockRegistryInterface */
    protected $stockRegistry;

    /**
     * @inheritdoc
     */
    protected function setupTests()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->mutableScopeConfig = $this->objectManager->create(MutableScopeConfigInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->collectionProvider = $this->objectManager->create(CollectionProvider::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $this->configHelper = $this->objectManager->create(ConfigurationHelper::class);
        $this->productHelper = $this->objectManager->create(ProductHelper::class);
        $this->stockRegistry = $this->objectManager->create(StockRegistryInterface::class);
        $this->configHelper->cleanConfig();
        $this->configHelper->setupDoofinder();
        $this->configHelper->setupConfigScenario1();
        $this->resetRegisterCollections('default');
    }

    /**
     * @inheritdoc
     */
    protected function tearDownTests()
    {
        $this->configHelper->cleanConfig();
        $this->resetRegisterCollections('default');
        $this->productHelper->deleteAllProducts();
    }

    private function resetRegisterCollections($storeCode) {
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $storeCode);
        $collection->walk('delete');

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_DELETE, $storeCode);
        $collection->walk('delete');
    }

    /**
     * Test register product update when stock status changes 
     *
     * @magentoApiDataFixture ../../../../app/code/Doofinder/Feed/Test/Integration/_files/product_simple.php
     */
    public function testEditProduct()
    {
        $fixtureProductId = 1;
        $fixtureProductSKU = 'simple';
        $fixtureStoreCode = 'default';
        $fixtureIsInStock = true;
        $fixtureStockItemId = $this->stockRegistry->getStockItem($fixtureProductId)->getItemId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace([':productSku', ':itemId'], [$fixtureProductSKU, $fixtureStockItemId], '/V1/products/:productSku/stockItems/:itemId'),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
        ];
        $requestData = [
            'productSku' => $fixtureProductSKU,
            'stockItem' => [
                'item_id' => $fixtureStockItemId,
                'product_id' => $fixtureProductId,
                'stock_id' => 1,
                'qty' => 1,
                'is_in_stock' => false,
                'is_qty_decimal' => false,
                'show_default_notification_message' => false,
                'use_config_min_qty' => true,
                'min_qty' => 0,
                'use_config_min_sale_qty' => 1,
                'min_sale_qty' => 1,
                'use_config_max_sale_qty' => true,
                'max_sale_qty' => 20,
                'use_config_backorders' => true,
                'backorders' => 0,
                'use_config_notify_stock_qty' => true,
                'notify_stock_qty' => 1,
                'use_config_qty_increments' => true,
                'qty_increments' => 0,
                'use_config_enable_qty_inc' => true,
                'enable_qty_increments' => false,
                'use_config_manage_stock' => true,
                'manage_stock' => true,
                'low_stock_date' => null,
                'is_decimal_divided' => false,
                'stock_status_changed_auto' => 0,
            ]
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $fixtureStoreCode);

        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($fixtureProductId, $item->getProductEntityId());
    }
}
