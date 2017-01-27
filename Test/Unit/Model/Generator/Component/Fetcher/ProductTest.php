<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Fetcher;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
     */
    private $_model;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $_productCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $_productCollectionFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator\ItemFactory
     */
    private $_generatorItemFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_product;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $_objectManagerHelper;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getEntityId'],
            [],
            '',
            false
        );

        $this->_productCollection = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection',
            [],
            [],
            '',
            false
        );
        $this->_productCollection->expects($this->any())->method('addAttributeToSelect')
            ->willReturn($this->_productCollection);
        $this->_productCollection->expects($this->any())->method('addStoreFilter')
            ->willReturn($this->_productCollection);
        $this->_productCollection->expects($this->any())->method('addAttributeToFilter')
            ->willReturn($this->_productCollection);
        $this->_productCollection->expects($this->any())->method('addAttributeToSort')
            ->willReturn($this->_productCollection);
        $this->_productCollection->expects($this->any())->method('getItems')
            ->willReturn(array($this->_product));
        $this->_productCollection->expects($this->any())->method('getLastItem')
            ->willReturn($this->_product);

        $this->_productCollectionFactory = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_productCollectionFactory->expects($this->any())->method('create')
            ->willReturn($this->_productCollection);

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );

        $this->_generatorItemFactory = $this->getMock(
            '\Doofinder\Feed\Model\Generator\ItemFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_generatorItemFactory->expects($this->any())->method('create')
            ->willReturn($this->_item);

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Fetcher\Product',
            [
                'productCollectionFactory' => $this->_productCollectionFactory,
                'generatorItemFactory' => $this->_generatorItemFactory
            ]
        );
    }

    /**
     * Test fetch
     */
    public function testFetch()
    {
        $items = $this->_model->fetch();

        $this->assertEquals([$this->_item], $items);
        $this->assertEquals(true, $this->_model->isStarted());
        $this->assertEquals(true, $this->_model->isDone());
    }

    /**
     * Test fetch with pagination
     */
    public function testFetchWithPagination()
    {
        $this->_model->setLimit(1);
        $this->_model->setOffset(2);

        $this->_productCollection->expects($this->once())->method('setPageSize')
            ->with(1)
            ->willReturn(array($this->_product));
        $this->_productCollection->expects($this->any())->method('addAttributeToFilter')
            ->withConsecutive(
                ['status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED],
                ['visibility', [
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
                ]],
                ['entity_id', ['gt' => 2]]
             )
            ->willReturn($this->_productCollection);

        $this->_model->fetch();
    }

    /**
     * Test fetch with offset transform
     */
    public function testFetchWithOffsetTransform()
    {
        $this->_model->setLimit(10);
        $this->_model->setOffset(30);
        $this->_model->setTransformOffset(true);

        $select = $this->getMock(
            '\Magento\Framework\DB\Select',
            [],
            [],
            '',
            false
        );
        $select->expects($this->once())->method('limit')->with(1, 29);
        $this->_productCollection->method('getSelect')->willReturn($select);

        $product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );
        $product->method('getEntityId')->willReturn(51);
        $this->_productCollection->method('fetchItem')->willReturn($product);

        $this->_productCollection->expects($this->once())->method('setPageSize')
            ->with(10)
            ->willReturn(array($this->_product));
        $this->_productCollection->expects($this->any())->method('addAttributeToFilter')
            ->withConsecutive(
                ['status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED],
                ['visibility', [
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
                ]],
                ['status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED],
                ['visibility', [
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
                ]],
                ['entity_id', ['gt' => 51]]
             )
            ->willReturn($this->_productCollection);

        $this->_model->fetch();
    }

    /**
     * Test isStarted() and isDone() methods
     *
     * @dataProvider testStartedDoneProvider
     */
    public function testStartedDone($offset, $size, $isStarted, $isDone)
    {
        $this->_productCollection->method('getSize')->willReturn($size);

        $this->_model->setLimit(1);
        $this->_model->setOffset($offset);

        $this->_model->fetch();

        $this->assertEquals($isStarted, $this->_model->isStarted());
        $this->assertEquals($isDone, $this->_model->isDone());
    }

    public function testStartedDoneProvider()
    {
        return [
            [0, 3, true, false],
            [1, 2, false, false],
            [2, 1, false, true],
        ];
    }

    /**
     * Test getLastProcessedEntityId() method
     */
    public function testGetLastProcessedEntityId()
    {
        $this->_product->method('getEntityId')->willReturn(11);
        $this->_productCollection->method('getSize')->willReturn(1);

        $this->_model->fetch();

        $this->assertEquals(11, $this->_model->getLastProcessedEntityId());
    }

    /**
     * Test getProgress() method
     */
    public function testGetProgress()
    {
        $this->_productCollection->method('getSize')->willReturn(20);

        $this->_model->setLimit(10);
        $this->_model->fetch();

        $this->_productCollection->method('getSize')->willReturn(10);
        $this->assertEquals(0.5, $this->_model->getProgress());
    }
}
