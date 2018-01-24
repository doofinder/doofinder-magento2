<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Fetcher\Product;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Fetcher\Product\Fixed
 */
class FixedTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher\Product\Fixed
     */
    private $model;

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
            \Doofinder\Feed\Model\Generator\Component\Fetcher\Product\Fixed::class,
            [
                'generatorItemFactory' => $this->generatorItemFactory,
                'data' => [
                    'products' => [$this->product],
                ],
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
        $this->item->expects($this->once())->method('setContext')->with($this->product);
        $items = $this->model->fetch();

        $this->assertEquals([$this->item], $items);
        $this->assertEquals(true, $this->model->isStarted());
        $this->assertEquals(true, $this->model->isDone());
    }
}
