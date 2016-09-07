<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Mapper
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    protected $_item;

    /**
     * @var \Doofinder\Feed\Model\Generator\Map
     */
    protected $_map;

    /**
     * @var \Doofinder\Feed\Model\Generator\MapFactory
     */
    protected $_mapFactory;

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

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            ['getContext'],
            [],
            '',
            false
        );
        $this->_item->method('getContext')->willReturn($this->_product);

        $this->_map = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Map',
            [],
            [],
            '',
            false
        );
        $map = [
            ['title', 'Sample title'],
            ['description', 'Sample description'],
        ];
        $this->_map->method('get')->will($this->returnValueMap($map));

        $this->_mapFactory = $this->getMock(
            '\Doofinder\Feed\Model\Generator\MapFactory',
            [],
            [],
            '',
            false
        );
        $this->_mapFactory->method('create')->willReturn($this->_map);

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Mapper',
            [
                'mapFactory' => $this->_mapFactory,
                'data' => [
                    'map' => [
                        'name' => 'title',
                        'desc' => 'description',
                    ]
                ]
            ]
        );
    }

    /**
     * Test process
     */
    public function testProcess()
    {
        $this->_model->process([$this->_item]);

        $this->assertEquals(
            [
                'name' => 'Sample title',
                'desc' => 'Sample description',
            ],
            $this->_item->getData()
        );
    }
}
