<?php

namespace Doofinder\Feed\Test\Integration\Plugin\CatalogSearch\Model\Indexer\Fulltext;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Doofinder\Feed\Model\ChangedProduct\Processor\CollectionProvider;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\FeedCompatibility\Test\Integration\BaseBackendController;

use Doofinder\Feed\Test\Helper\Configuration as ConfigurationHelper;
use Doofinder\Feed\Test\Helper\Product as ProductHelper;

/**
 * Provide tests for the plugin.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ProductBackendCrudTest extends BaseBackendController
{
    /** @var ObjectManagerInterface */
    private $objectManager;

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
    protected function setupTests()
    {
        $this->objectManager = Bootstrap::getObjectManager();
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
    protected function tearDownTests()
    {
        $this->configHelper->cleanConfig();
        $this->resetRegisterCollections('default');
        $this->productHelper->deleteAllProducts();
    }
    /**
     * Test create product with already existing url key.
     *
     * @dataProvider saveActionWithAlreadyExistingUrlKeyDataProvider
     * @magentoDbIsolation disabled
     * 
     * @param array $postData
     * @return void
     */
    public function testCreateProduct(array $postData)
    {
        $fixtureProductId = 11111;
        $fixtureStoreCode = 'default';
        
        $this->productHelper->deleteAllProducts();

        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save');

        $product = $this->productRepository->get('s2');

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $fixtureStoreCode);

        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($product->getId(), $item->getProductEntityId());
    }
    /**
     * Provide test data for testSaveActionWithAlreadyExistingUrlKey().
     *
     * @return array
     */
    public function saveActionWithAlreadyExistingUrlKeyDataProvider()
    {
        return [
            [
                'post_data' => [
                    'product' =>
                        [
                            'attribute_set_id' => '4',
                            'status' => '1',
                            'name' => 's2',
                            'url_key' => 'simple-product',
                            'quantity_and_stock_status' =>
                                [
                                    'qty' => '10',
                                    'is_in_stock' => '1',
                                ],
                            'website_ids' =>
                                [
                                    1 => '1',
                                ],
                            'sku' => 's2',
                            'price' => '3',
                            'tax_class_id' => '2',
                            'product_has_weight' => '0',
                            'visibility' => '4',
                            'type_id' => 'simple',
                        ],
                ]
            ]
        ];
    }
    /**
     * Test edit product
     * 
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture ../../../../app/code/Doofinder/Feed/Test/Integration/_files/product_simple.php
     */
    public function testEditProduct()
    {
        $fixtureProductId = 1;
        $fixtureStoreCode = 'default';

        $product = $this->productRepository->getById($fixtureProductId);
        $this->getRequest()->setPostValue([
            'id' => $fixtureProductId,
            'product' => [
                'name' => $product->getName().'-test'
            ]
        ]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/'.$fixtureProductId);

        $product = $this->productRepository->getById($fixtureProductId);

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $fixtureStoreCode);

        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($product->getId(), $item->getProductEntityId());
    }
    /**
     * Test delete product
     * 
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture ../../../../app/code/Doofinder/Feed/Test/Integration/_files/product_simple.php
     */
    public function testDeleteProduct()
    {
        $fixtureProductId = 1;
        $fixtureStoreCode = 'default';
        $fixtureSKU = 'simple';
        
        $this->getRequest()->setPostValue([
            'selected' => [$fixtureProductId],
            'filters' => ['placeholder' => true],
            'search' => '',
            'namespace' => 'product_listing'
        ]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/massdelete');

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_DELETE, $fixtureStoreCode);

        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($fixtureProductId, $item->getProductEntityId());
    }

    private function resetRegisterCollections($storeCode) {
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $storeCode);
        $collection->walk('delete');

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_DELETE, $storeCode);
        $collection->walk('delete');
    }

    
}