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
            [],
            [],
            '',
            false
        );

        $this->_productCollection = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection',
            [
                'load',
                'addAttributeToSelect',
                'addStoreFilter',
                'setPageSize',
                'setCurPage',
                'addAttributeToSort',
                'getLastPageNumber',
            ],
            [],
            '',
            false
        );
        $this->_productCollection->expects($this->any())->method('addAttributeToSelect')
            ->willReturn($this->_productCollection);
        $this->_productCollection->expects($this->any())->method('addStoreFilter')
            ->willReturn($this->_productCollection);
        $this->_productCollection->expects($this->any())->method('addAttributeToSort')
            ->willReturn($this->_productCollection);
        $this->_productCollection->expects($this->any())->method('load')
            ->willReturn(array($this->_product));
        $this->_productCollection->expects($this->any())->method('getLastPageNumber')
            ->willReturn(3);

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
        $this->_model->setPageSize(1);
        $this->_model->setCurPage(2);

        $this->_productCollection->expects($this->once())->method('setPageSize')
            ->with(1)
            ->willReturn(array($this->_product));
        $this->_productCollection->expects($this->once())->method('setCurPage')
            ->with(2)
            ->willReturn($this->_productCollection);

        $this->_model->fetch();
    }

    /**
     * Test isStarted() and isDone() methods
     *
     * @dataProvider testStartedDoneProvider
     */
    public function testStartedDone($page, $isStarted, $isDone)
    {
        $this->_model->setPageSize(1);
        $this->_model->setCurPage($page);

        $this->_model->fetch();

        $this->assertEquals($isStarted, $this->_model->isStarted());
        $this->assertEquals($isDone, $this->_model->isDone());
    }

    public function testStartedDoneProvider()
    {
        return [
            [1, true, false],
            [2, false, false],
            [3, false, true],
        ];
    }
}
