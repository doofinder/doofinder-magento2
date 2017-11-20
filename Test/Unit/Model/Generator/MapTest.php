<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

use Doofinder\Feed\Test\Unit\BaseTestCase;

class MapTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map
     */
    private $_model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $_context;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_context = $this->getMock(
            '\Magento\Framework\DataObject',
            [],
            [],
            '',
            false
        );
        $map = [
            ['title', null, 'Sample title'],
            ['description', null, 'Sample description'],
        ];
        $this->_context->method('getData')->will($this->returnValueMap($map));

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );
        $this->_item->method('getContext')->willReturn($this->_context);

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Generator\Map',
            [
                'item' => $this->_item,
            ]
        );
    }

    /**
     * Test get() method
     */
    public function testGet()
    {
        $this->assertEquals('Sample title', $this->_model->get('title'));
        $this->assertEquals('Sample description', $this->_model->get('description'));
    }
}
