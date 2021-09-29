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
class ProductPricesUpdateTest extends BaseWebapi
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
    public function testEditProductBasePriceTest()
    {
        $fixtureProductId = 1;
        $fixtureProductSKU = 'simple';
        $fixtureStoreCode = 'default';
        $fixtureStoreId = 1;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/base-prices',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = [
            'prices' => [
                [
                    'price' => 22,
                    'sku' => $fixtureProductSKU
                ]
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
