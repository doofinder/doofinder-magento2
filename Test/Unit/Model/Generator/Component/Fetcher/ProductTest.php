<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Fetcher;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
 */
class ProductTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher\Product
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator\ItemFactory
     */
    private $generatorItemFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getEntityId'],
            [],
            '',
            false
        );

        $this->productCollection = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->productCollection->expects($this->any())->method('addAttributeToSelect')
            ->willReturn($this->productCollection);
        $this->productCollection->expects($this->any())->method('addStoreFilter')
            ->willReturn($this->productCollection);
        $this->productCollection->expects($this->any())->method('addAttributeToFilter')
            ->willReturn($this->productCollection);
        $this->productCollection->expects($this->any())->method('addAttributeToSort')
            ->willReturn($this->productCollection);
        $this->productCollection->expects($this->any())->method('getItems')
            ->willReturn([$this->product]);
        $this->productCollection->expects($this->any())->method('getLastItem')
            ->willReturn($this->product);

        $this->productCollectionFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->productCollectionFactory->expects($this->any())->method('create')
            ->willReturn($this->productCollection);

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );

        $this->generatorItemFactory = $this->getMock(
            \Doofinder\Feed\Model\Generator\ItemFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->generatorItemFactory->expects($this->any())->method('create')
            ->willReturn($this->item);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Component\Fetcher\Product::class,
            [
                'productColFactory' => $this->productCollectionFactory,
                'generatorItemFactory' => $this->generatorItemFactory
            ]
        );
    }

    /**
     * Test fetch() method
     *
     * @return void
     */
    public function testFetch()
    {
        $items = $this->model->fetch();

        $this->assertEquals([$this->item], $items);
        $this->assertEquals(true, $this->model->isStarted());
        $this->assertEquals(true, $this->model->isDone());
    }

    /**
     * Test fetch() method with pagination
     *
     * @return void
     */
    public function testFetchWithPagination()
    {
        $this->model->setLimit(1);
        $this->model->setOffset(2);

        $this->productCollection->expects($this->once())->method('setPageSize')
            ->with(1)
            ->willReturn([$this->product]);
        $this->productCollection->expects($this->any())->method('addAttributeToFilter')
            ->withConsecutive(
                ['status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED],
                ['visibility', [
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
                ]],
                ['entity_id', ['gt' => 2]]
            )
            ->willReturn($this->productCollection);

        $this->model->fetch();
    }

    /**
     * Test fetch() method with offset transform
     *
     * @return void
     */
    public function testFetchWithOffsetTransform()
    {
        $this->model->setLimit(10);
        $this->model->setOffset(30);
        $this->model->setTransformOffset(true);

        $select = $this->getMock(
            \Magento\Framework\DB\Select::class,
            [],
            [],
            '',
            false
        );
        $this->productCollection->method('getSelect')->willReturn($select);

        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [],
            [],
            '',
            false
        );
        $product->method('getEntityId')->willReturn(51);

        $this->productCollection->expects($this->once())->method('getAllIds')
            ->with(1, 29)
            ->willReturn([$product->getEntityId()]);
        $this->productCollection->expects($this->any())->method('addAttributeToFilter')
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
            ->willReturn($this->productCollection);

        $this->model->fetch();
    }

    /**
     * Test isStarted() and isDone() methods
     *
     * @param  integer $offset
     * @param  integer $size
     * @param  boolean $isStarted
     * @param  boolean $isDone
     * @return void
     * @dataProvider providerTestStartedDone
     */
    public function testStartedDone($offset, $size, $isStarted, $isDone)
    {
        $this->productCollection->method('getSize')->willReturn($size);

        $this->model->setLimit(1);
        $this->model->setOffset($offset);

        $this->model->fetch();

        $this->assertEquals($isStarted, $this->model->isStarted());
        $this->assertEquals($isDone, $this->model->isDone());
    }

    /**
     * Data provider for testStartedDone() test
     *
     * @return array
     */
    public function providerTestStartedDone()
    {
        return [
            [0, 3, true, false],
            [1, 2, false, false],
            [2, 1, false, true],
        ];
    }

    /**
     * Test getLastProcessedEntityId() method
     *
     * @return void
     */
    public function testGetLastProcessedEntityId()
    {
        $this->product->method('getEntityId')->willReturn(11);
        $this->productCollection->method('getSize')->willReturn(1);

        $this->model->fetch();

        $this->assertEquals(11, $this->model->getLastProcessedEntityId());
    }

    /**
     * Test getProgress() method
     *
     * @return void
     */
    public function testGetProgress()
    {
        $this->productCollection->method('getSize')->willReturn(20);

        $this->model->setLimit(10);
        $this->model->fetch();

        $this->productCollection->method('getSize')->willReturn(10);
        $this->assertEquals(0.5, $this->model->getProgress());
    }
}
