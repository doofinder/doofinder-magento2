<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

class MapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map
     */
    protected $_model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    protected $_item;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_context;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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

        $this->_model = $this->_objectManagerHelper->getObject(
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
