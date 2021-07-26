<?php

namespace Doofinder\Feed\Test\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Framework\App\Request\Http as HttpRequest;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedProduct\Processor\CollectionProvider;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;

use Doofinder\Feed\Test\Helper\Configuration as ConfigurationHelper;
use Doofinder\Feed\Test\Helper\Product as ProductHelper;

/**
 * Provide tests for the plugin.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class ProductCrud extends \Magento\TestFramework\TestCase\WebapiAbstract
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->mutableScopeConfig = $this->objectManager->create(MutableScopeConfigInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->collectionProvider = $this->objectManager->create(CollectionProvider::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $this->configHelper = $this->objectManager->create(ConfigurationHelper::class);
        $this->productHelper = $this->objectManager->create(ProductHelper::class);
        $this->configHelper->cleanConfig();
        $this->configHelper->setupDoofinder();
        $this->configHelper->setupConfigScenario1();
        $this->resetRegisterCollections('default');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->configHelper->cleanConfig();
        $this->resetRegisterCollections('default');
        //$this->deleteProducts([$fixtureSKU]);
        $this->productHelper->deleteAllProducts();
        parent::tearDown();
    }

    private function resetRegisterCollections($storeCode) {
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $storeCode);
        $collection->walk('delete');

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_DELETE, $storeCode);
        $collection->walk('delete');
    }

    public function testCreateProduct()
    {
        $fixtureStoreCode = 'default';
        $fixtureSKU = 'simple-product';

        //$this->productHelper->deleteAllProducts();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = [
            'product' => [
                'sku' => $fixtureSKU,
                'name' => 'Postman Simple Product 2-2',
                'attribute_set_id' => 4,
                'price' => 12,
                'status' => 1,
                'visibility' => 4,
                'type_id' => 'simple',
                'extension_attributes' => [
                    'category_links' => [[
                        'position' => 0,
                        'category_id' => 2,
                    ]],
                    'stock_item' => [
                        'qty' => 12,
                        'is_in_stock' => 1,
                    ],
                ],
            ],
            'saveOptions' => 1
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);

        $product = $this->productRepository->get($fixtureSKU);
        
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $fixtureStoreCode);

        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($product->getId(), $item->getProductEntityId());
    }

    /**
     * Test register product on edit action
     *
     * @magentoApiDataFixture ../../../../app/code/Doofinder/Feed/Test/Integration/_files/product_simple.php
     */
    public function testEditProduct()
    {
        $fixtureProductId = 1;
        $fixtureStoreCode = 'default';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = [
            'product' => [
                'id' => $fixtureProductId,
                'sku' => 'simple',
                'name' => 'Test',
            ]
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);

        $product = $this->productRepository->getById($fixtureProductId);
        
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $fixtureStoreCode);

        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($product->getId(), $item->getProductEntityId());
    }

    /**
     * Test register product on delete action
     *
     * @magentoApiDataFixture ../../../../app/code/Doofinder/Feed/Test/Integration/_files/product_simple.php
     */
    public function testDeleteProduct()
    {
        $fixtureProductId = 1;
        $fixtureSKU = 'simple';
        $fixtureStoreCode = 'default';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/'.$fixtureSKU,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, []);

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_DELETE, $fixtureStoreCode);

        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($fixtureProductId, $item->getProductEntityId());
    }
}
