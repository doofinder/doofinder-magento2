<?php

namespace Doofinder\Feed\Test\Integration\Plugin\CatalogSearch\Model\Indexer\Fulltext;

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
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Doofinder\FeedCompatibility\Test\Integration\Base;

use Doofinder\Feed\Test\Helper\Configuration as ConfigurationHelper;
use Doofinder\Feed\Test\Helper\Product as ProductHelper;

/**
 * Provide tests for the plugin.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ProductTest extends Base
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
    protected function setupTests()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->mutableScopeConfig = $this->objectManager->create(MutableScopeConfigInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->collectionProvider = $this->objectManager->create(CollectionProvider::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $this->configHelper = $this->objectManager->create(ConfigurationHelper::class);
        $this->productHelper = $this->objectManager->create(ProductHelper::class);
        $this->configHelper->cleanConfig();
    }

    /**
     * @inheritdoc
     */
    protected function tearDownTests()
    {
        $this->configHelper->cleanConfig();
        $this->productHelper->deleteAllProducts();
    }

    /**
     * Test register product save
     * 
     * @magentoDataFixture ../../../../app/code/Doofinder/Feed/Test/Integration/_files/product_simple.php
     */
    public function testRegisterOnProductSave(): void
    {
        $fixtureProductId = 1;
        $fixtureStoreCode = 'default';

        $this->resetRegisterCollections($fixtureStoreCode);
        $this->configHelper->setupConfigScenario1();
        

        $product = $this->productRepository->getById($fixtureProductId);
        $product->setName($product->getName().'-test');
        $product->save();

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $this->storeManager->getStore($product->getStoreId())->getCode());
        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($product->getId(), $item->getProductEntityId());

        $this->resetRegisterCollections($fixtureStoreCode);
        $this->configHelper->cleanConfig();
    }

    /**
     * Test register product create
     */
    public function testRegisterOnProductCreate(): void
    {
        $fixtureProductId = 11111;
        $fixtureStoreCode = 'default';

        $this->productHelper->deleteAllProducts();
        $this->resetRegisterCollections($fixtureStoreCode);
        $this->configHelper->setupConfigScenario1();

        $product = $this->createProduct($fixtureProductId);

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $this->storeManager->getStore($product->getStoreId())->getCode());

        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($fixtureProductId, $item->getProductEntityId());

        $this->resetRegisterCollections($fixtureStoreCode);
        $this->configHelper->cleanConfig();
        $product->delete();
    }

    /**
     * Test register product delete
     */
    public function testRegisterOnProductDelete(): void
    {
        $fixtureProductId = 11111;
        $fixtureStoreCode = 'default';

        $this->productHelper->deleteAllProducts();
        $this->resetRegisterCollections($fixtureStoreCode);
        $this->configHelper->setupConfigScenario1();

        
        $product = $this->createProduct($fixtureProductId);
        $product->delete();
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_DELETE, $fixtureStoreCode);
        $items = $collection->getItems();
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertEquals($fixtureProductId, $item->getProductEntityId());
        $this->resetRegisterCollections($fixtureStoreCode);
        $this->configHelper->cleanConfig();
    }

    private function createProduct($identifier) 
    {
        $adminWebsite = $this->objectManager->get(\Magento\Store\Api\WebsiteRepositoryInterface::class)->get('admin');
        /** @var  $productExtensionAttributes */
        $productExtensionAttributesFactory = $this->objectManager->get(ProductExtensionInterfaceFactory::class);
        $productExtensionAttributesWebsiteIds = $productExtensionAttributesFactory->create(
            ['website_ids' => $adminWebsite->getId()]
        );
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->isObjectNew(true);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setId($identifier)
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Simple Product')
            ->setSku('simple')
            ->setPrice(10)
            ->setWeight(1)
            ->setShortDescription("Short description")
            ->setTaxClassId(0)
            ->setDescription('Description with <b>html tag</b>')
            ->setExtensionAttributes($productExtensionAttributesWebsiteIds)
            ->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
          
            ->setStockData(
                [
                    'use_config_manage_stock'   => 1,
                    'qty'                       => 100,
                    'is_qty_decimal'            => 0,
                    'is_in_stock'               => 1,
                ]
            );

        $product->save();
        return $product;
    }

    private function resetRegisterCollections($storeCode) {
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $storeCode);
        $collection->walk('delete');

        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_DELETE, $storeCode);
        $collection->walk('delete');
    }

    
}