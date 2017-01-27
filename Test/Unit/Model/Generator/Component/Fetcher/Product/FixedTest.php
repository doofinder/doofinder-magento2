<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Fetcher\Product;

class FixedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher\Product\Fixed
     */
    private $_model;

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
            '\Doofinder\Feed\Model\Generator\Component\Fetcher\Product\Fixed',
            [
                'generatorItemFactory' => $this->_generatorItemFactory,
                'data' => [
                    'products' => [$this->_product],
                ],
            ]
        );
    }

    /**
     * Test fetch
     */
    public function testFetch()
    {
        $this->_item->expects($this->once())->method('setContext')->with($this->_product);
        $items = $this->_model->fetch();

        $this->assertEquals([$this->_item], $items);
        $this->assertEquals(true, $this->_model->isStarted());
        $this->assertEquals(true, $this->_model->isDone());
    }
}
