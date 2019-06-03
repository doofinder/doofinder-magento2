<?php

namespace Doofinder\Feed\Test\Integration\Model\Generator\Component\Fetcher;

use Magento\TestFramework\TestCase\AbstractIntegrity;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
 *
 * @codingStandardsIgnoreStart
 * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
 * @codingStandardsIgnoreEnd
 */
class ProductTest extends AbstractIntegrity
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    private $stockStatusResource;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->stockStatusResource = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::class
        )->disableOriginalConstructor()
        ->disableOriginalClone()
        ->disableArgumentCloning()
        ->getMock();

        $this->model = $this->objectManager->create(
            \Doofinder\Feed\Model\Generator\Component\Fetcher\Product::class,
            [
                'stockStatusResource' => $this->stockStatusResource,
            ]
        );
    }

    /**
     * Set products visible and in catalog
     *
     * @return void
     */
    private function makeProductsFetchable()
    {
        $collection = $this->objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        )->create();

        foreach ($collection->load() as $product) {
            $product
                ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                // @codingStandardsIgnoreStart
                ->save();
                // @codingStandardsIgnoreEnd
        }
    }

    /**
     * Test fetch() method filtering
     *
     * @return void
     */
    public function testFetchFilters()
    {
        $items = $this->model->fetch();

        $this->assertEquals(1, count($items));
        $this->assertEquals(
            [
                'simple1',
            ],
            array_map(function ($item) {
                return $item->getContext()->getSku();
            }, $items)
        );

        $this->assertEquals(true, $this->model->isStarted());
        $this->assertEquals(true, $this->model->isDone());
    }

    /**
     * Test fetch() method
     *
     * @return void
     */
    public function testFetch()
    {
        $this->makeProductsFetchable();

        $items = $this->model->fetch();

        $this->assertEquals(3, count($items));
        $this->assertEquals(
            [
                'simple1',
                'simple2',
                'simple3',
            ],
            array_map(function ($item) {
                return $item->getContext()->getSku();
            }, $items)
        );

        $this->assertEquals(true, $this->model->isStarted());
        $this->assertEquals(true, $this->model->isDone());
    }

    /**
     * Test fetch() method with pagination
     *
     * @param  boolean $useOffset
     * @param  string $sku
     * @param  boolean $isStarted
     * @param  boolean $isDone
     * @return void
     * @dataProvider providerFetchWithPagination
     */
    public function testFetchWithPagination($useOffset, $sku, $isStarted, $isDone)
    {
        $this->makeProductsFetchable();

        $entityId = $this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        )->get($sku)->getEntityId();

        $this->model->setOffset($useOffset ? $entityId - 1 : null);
        $this->model->setLimit(1);

        $items = $this->model->fetch();

        $this->assertEquals(1, count($items));
        $this->assertEquals($sku, $items[0]->getContext()->getSku());

        $this->assertEquals($isStarted, $this->model->isStarted());
        $this->assertEquals($isDone, $this->model->isDone());
    }

    /**
     * Data provider for fetchWithPagination() test
     *
     * @return array
     */
    public function providerFetchWithPagination()
    {
        return [
            [false, 'simple1', true, false],
            [true, 'simple2', false, false],
            [true, 'simple3', false, true],
        ];
    }

    /**
     * Test fetch() method with multiple websites
     *
     * @return void
     * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testFetchMultiwebsite()
    {
        $this->makeProductsFetchable();

        /** @var \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositoryFactory */
        $websiteRepFactory = $this->objectManager->create(
            \Magento\Store\Api\WebsiteRepositoryInterface::class
        );

        $website = $websiteRepFactory->get('test');

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepFactory */
        $productRepFactory = $this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );

        $product = $productRepFactory->get('simple1');
        $product->setWebsiteIds([$website->getId()])
            ->save();

        $items = $this->model->fetch();

        $this->assertEquals(2, count($items));
        $this->assertEquals(
            [
                'simple2',
                'simple3',
            ],
            array_map(function ($item) {
                return $item->getContext()->getSku();
            }, $items)
        );

        $this->assertEquals(true, $this->model->isStarted());
        $this->assertEquals(true, $this->model->isDone());
    }

    /**
     * Test fetch() method with configurable product
     *
     * @return void
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testFetchConfigurable()
    {
        $items = $this->model->fetch();

        $this->assertEquals(3, count($items));
        $this->assertEquals(
            [
                'configurable',
                'simple_10',
                'simple_20',
            ],
            array_map(function ($item) {
                return $item->getContext()->getSku();
            }, $items)
        );

        $this->assertEquals(
            [
                'simple_10',
                'simple_20',
            ],
            array_map(function ($item) {
                return $item->getContext()->getSku();
            }, $items[0]->getAssociates())
        );

        $this->assertEquals(true, $this->model->isStarted());
        $this->assertEquals(true, $this->model->isDone());
    }

    /**
     * Test getLastProcessedEntityId() method
     *
     * @return void
     */
    public function testGetLastProcessedEntityId()
    {
        $this->makeProductsFetchable();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepFactory */
        $productRepFactory = $this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );

        $product = $productRepFactory->get('simple3');

        $this->model->fetch();

        $this->assertEquals($product->getEntityId(), $this->model->getLastProcessedEntityId());
    }

    /**
     * Test getProgress() method
     *
     * @return void
     */
    public function testGetProgress()
    {
        $this->makeProductsFetchable();

        $this->model->setLimit(2);
        $this->model->fetch();

        $this->assertEquals(0.67, $this->model->getProgress());
    }
}
