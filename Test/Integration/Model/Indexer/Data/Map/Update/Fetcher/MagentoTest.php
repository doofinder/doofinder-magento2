<?php

namespace Doofinder\Feed\Test\Integration\Model\Indexer\Data\Map\Update\Fetcher;

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
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;

use Magento\Indexer\Model\Indexer;

use Doofinder\Feed\Test\Helper\Configuration as ConfigurationHelper;
use Doofinder\Feed\Test\Helper\Product as ProductHelper;
use Doofinder\Feed\Model\Indexer\Data\Mapper;

/**
 * Provide tests for the plugin.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ProductTest extends \PHPUnit\Framework\TestCase
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

    /** @var Mapper */
    private $mapper;

    /** @var Indexer */
    private $indexer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->mutableScopeConfig = $this->objectManager->create(MutableScopeConfigInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->collectionProvider = $this->objectManager->create(CollectionProvider::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $this->configHelper = $this->objectManager->create(ConfigurationHelper::class);
        $this->productHelper = $this->objectManager->create(ProductHelper::class);
        $this->mapper = $this->objectManager->create(Mapper::class);
        $this->indexer = $this->objectManager->create(Indexer::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
    }

    /**
     * Test register product save
     * 
     * @magentoDataFixture ../../../../app/code/Doofinder/Feed/Test/Integration/_files/attribute_for_caching.php
     * @magentoDataFixture ../../../../app/code/Doofinder/Feed/Test/Integration/_files/product_simple.php
     */
    public function testMapCustomAttribute(): void
    {
        $fixtureProductId = 1;
        $fixtureStoreId = 0;
        $fixtureAttributeCode = 'foo';
        
        $product = $this->productRepository->getById($fixtureProductId);
        $product->setData($fixtureAttributeCode, 'test');
        $product->save();
        $this->reindexAll();

        $batchDocuments = [
            $fixtureProductId => [],
        ];


        $docs = $this->mapper->get('update')->map($batchDocuments, $product->getStoreId());

        $this->assertNotNull($docs);
        $this->assertEquals(1, count($docs));
        $doc = current($docs);
        $this->assertTrue(isset($doc[$fixtureAttributeCode]));
    }

    private function reindexAll() {
        $this->indexer->load('catalogsearch_fulltext');
        $this->indexer->reindexAll();
    }
}