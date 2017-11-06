<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Fetcher\Product;

use Doofinder\Feed\Test\Unit\BaseTestCase;

class FixedTest extends BaseTestCase
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
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

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

        $this->_model = $this->objectManager->getObject(
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
