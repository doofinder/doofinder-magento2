<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

class MapperTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Mapper
     */
    private $_model;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_product;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * @var \Doofinder\Feed\Model\Generator\Map
     */
    private $_map;

    /**
     * @var \Doofinder\Feed\Model\Generator\MapFactory
     */
    private $_mapFactory;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

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
        $this->_map->expects($this->once())->method('before');
        $this->_map->expects($this->once())->method('after');

        $this->_mapFactory = $this->getMock(
            '\Doofinder\Feed\Model\Generator\MapFactory',
            [],
            [],
            '',
            false
        );
        $this->_mapFactory->method('create')->willReturn($this->_map);

        $this->_model = $this->objectManager->getObject(
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
