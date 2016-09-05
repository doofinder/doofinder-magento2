<?php

namespace Doofinder\Feed\Test\Integration\Model\Generator\Component\Fetcher;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
 *
 * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
     */
    private $_model;

    public function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Doofinder\Feed\Model\Generator\Component\Fetcher\Product'
        );
    }

    /**
     * Set products visible and in catalog
     */
    protected function makeProductsFetchable()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory'
        )->create();

        foreach ($collection->load() as $product) {
            $product
                ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->save();
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
     * @dataProvider testFetchWithPaginationProvider
     */
    public function testFetchWithPagination($useOffset, $sku, $isStarted, $isDone)
    {
        $this->makeProductsFetchable();

        $entityId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
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

    public function testFetchWithPaginationProvider()
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
        $websiteRepositoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Store\Api\WebsiteRepositoryInterface'
        );

        $website = $websiteRepositoryFactory->get('test');

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
        $productRepositoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Catalog\Api\ProductRepositoryInterface'
        );

        $product = $productRepositoryFactory->get('simple1');
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
     * Test getLastProcessedEntityId() method
     */
    public function testGetLastProcessedEntityId()
    {
        $this->makeProductsFetchable();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
        $productRepositoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Catalog\Api\ProductRepositoryInterface'
        );

        $product = $productRepositoryFactory->get('simple3');

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
