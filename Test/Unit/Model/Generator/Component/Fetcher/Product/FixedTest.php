<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Fetcher\Product;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Fetcher\Product\Fixed
 */
class FixedTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
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

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->item = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generatorItemFactory = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\ItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
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
