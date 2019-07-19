<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Mapper
 */
class MapperTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
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

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->item = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Item::class)
            ->setMethods(['getContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->item->method('getContext')->willReturn($this->product);

        $this->map = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Map::class)
            ->disableOriginalConstructor()
            ->getMock();
        $map = [
            ['title', 'Sample title'],
            ['description', 'Sample description'],
        ];
        $this->map->method('get')->will($this->returnValueMap($map));
        $this->map->expects($this->once())->method('before');
        $this->map->expects($this->once())->method('after');

        $this->mapFactory = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\MapFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
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
