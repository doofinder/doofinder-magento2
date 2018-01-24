<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Mapper
 */
class MapperTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Mapper
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * @var \Doofinder\Feed\Model\Generator\Map
     */
    private $map;

    /**
     * @var \Doofinder\Feed\Model\Generator\MapFactory
     */
    private $mapFactory;

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
            [],
            [],
            '',
            false
        );

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            ['getContext'],
            [],
            '',
            false
        );
        $this->item->method('getContext')->willReturn($this->product);

        $this->map = $this->getMock(
            \Doofinder\Feed\Model\Generator\Map::class,
            [],
            [],
            '',
            false
        );
        $map = [
            ['title', 'Sample title'],
            ['description', 'Sample description'],
        ];
        $this->map->method('get')->will($this->returnValueMap($map));
        $this->map->expects($this->once())->method('before');
        $this->map->expects($this->once())->method('after');

        $this->mapFactory = $this->getMock(
            \Doofinder\Feed\Model\Generator\MapFactory::class,
            [],
            [],
            '',
            false
        );
        $this->mapFactory->method('create')->willReturn($this->map);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Component\Processor\Mapper::class,
            [
                'mapFactory' => $this->mapFactory,
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
     * Test process() method
     *
     * @return void
     */
    public function testProcess()
    {
        $this->model->process([$this->item]);

        $this->assertEquals(
            [
                'name' => 'Sample title',
                'desc' => 'Sample description',
            ],
            $this->item->getData()
        );
    }
}
