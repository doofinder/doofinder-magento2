<?php

namespace Doofinder\Feed\Test\Integration\Model\Generator\Component\Fetcher;

use Magento\TestFramework\TestCase\AbstractIntegrity;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
 *
 * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
 */
class ProductTest extends AbstractIntegrity
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
     */
    private $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_model = $this->_objectManager->create(
            '\Doofinder\Feed\Model\Generator\Component\Fetcher\Product'
        );
    }

    /**
     * Set products visible and in catalog
     */
    private function makeProductsFetchable()
    {
        $collection = $this->_objectManager->create(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory'
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
     */
    public function testFetchFilters()
    {
        $items = $this->_model->fetch();

        $this->assertEquals(1, count($items));
        $this->assertEquals(
            [
                'simple1',
            ],
            array_map(function ($item) {
                return $item->getContext()->getSku();
            }, $items)
        );

        $this->assertEquals(true, $this->_model->isStarted());
        $this->assertEquals(true, $this->_model->isDone());
    }

    /**
     * Test fetch() method
     */
    public function testFetch()
    {
        $this->makeProductsFetchable();

        $items = $this->_model->fetch();

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

        $this->assertEquals(true, $this->_model->isStarted());
        $this->assertEquals(true, $this->_model->isDone());
    }

    /**
     * Test fetch() method with pagination
     *
     * @dataProvider providerTestFetchWithPagination
     */
    public function testFetchWithPagination($useOffset, $sku, $isStarted, $isDone)
    {
        $this->makeProductsFetchable();

        $entityId = $this->_objectManager->create(
            '\Magento\Catalog\Api\ProductRepositoryInterface'
        )->get($sku)->getEntityId();

        $this->_model->setOffset($useOffset ? $entityId - 1 : null);
        $this->_model->setLimit(1);

        $items = $this->_model->fetch();

        $this->assertEquals(1, count($items));
        $this->assertEquals($sku, $items[0]->getContext()->getSku());

        $this->assertEquals($isStarted, $this->_model->isStarted());
        $this->assertEquals($isDone, $this->_model->isDone());
    }

    public function providerTestFetchWithPagination()
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
     * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testFetchMultiwebsite()
    {
        $this->makeProductsFetchable();

        /** @var \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositoryFactory */
        $websiteRepFactory = $this->_objectManager->create(
            '\Magento\Store\Api\WebsiteRepositoryInterface'
        );

        $website = $websiteRepFactory->get('test');

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepFactory */
        $productRepFactory = $this->_objectManager->create(
            '\Magento\Catalog\Api\ProductRepositoryInterface'
        );

        $product = $productRepFactory->get('simple1');
        $product->setWebsiteIds([$website->getId()])
            ->save();

        $items = $this->_model->fetch();

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

        $this->assertEquals(true, $this->_model->isStarted());
        $this->assertEquals(true, $this->_model->isDone());
    }

    /**
     * Test fetch() method with configurable product
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testFetchConfigurable()
    {
        $items = $this->_model->fetch();

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

        $this->assertEquals(true, $this->_model->isStarted());
        $this->assertEquals(true, $this->_model->isDone());
    }

    /**
     * Test getLastProcessedEntityId() method
     */
    public function testGetLastProcessedEntityId()
    {
        $this->makeProductsFetchable();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepFactory */
        $productRepFactory = $this->_objectManager->create(
            '\Magento\Catalog\Api\ProductRepositoryInterface'
        );

        $product = $productRepFactory->get('simple3');

        $this->_model->fetch();

        $this->assertEquals($product->getEntityId(), $this->_model->getLastProcessedEntityId());
    }

    /**
     * Test getProgress() method
     */
    public function testGetProgress()
    {
        $this->makeProductsFetchable();

        $this->_model->setLimit(2);
        $this->_model->fetch();

        $this->assertEquals(0.67, $this->_model->getProgress());
    }
}
