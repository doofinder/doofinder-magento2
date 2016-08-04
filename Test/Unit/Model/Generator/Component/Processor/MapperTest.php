<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Map
     */
    private $_model;

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

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            null,
            [
                'data' => [
                    'title' => 'Sample title',
                    'description' => 'Sample description',
                ],
            ]
        );

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Mapper',
            [
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
