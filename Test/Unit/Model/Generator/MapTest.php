<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Map
 */
class MapTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map
     */
    private $model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $context;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->context = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $map = [
            ['title', null, 'Sample title'],
            ['description', null, 'Sample description'],
        ];
        $this->context->method('getData')->will($this->returnValueMap($map));

        $this->item = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->item->method('getContext')->willReturn($this->context);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Map::class,
            [
                'item' => $this->item,
            ]
        );
    }

    /**
     * Test get() method
     *
     * @return void
     */
    public function testGet()
    {
        $this->assertEquals('Sample title', $this->model->get('title'));
        $this->assertEquals('Sample description', $this->model->get('description'));
    }
}
