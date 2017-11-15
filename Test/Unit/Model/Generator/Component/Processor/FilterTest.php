<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

use Doofinder\Feed\Test\Unit\BaseTestCase;

class FilterTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Filter
     */
    private $_model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item[]
     */
    private $_items;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $items = [
            [
                'title' => 'Sample product',
                'description' => 'Sample description',
            ],
            [
                'title' => '',
                'description' => 'Sample description',
            ],
            [
                'title' => null,
                'description' => '',
            ],
        ];

        $this->_items = [];

        foreach ($items as $item) {
            array_push($this->_items, $this->getMock(
                '\Doofinder\Feed\Model\Generator\Item',
                null,
                ['data' => $item]
            ));
        }

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Filter',
            []
        );
    }

    /**
     * Test process
     */
    public function testProcess()
    {
        $this->_model->process($this->_items);

        $this->assertFalse($this->_items[0]->isSkip());
        $this->assertFalse($this->_items[1]->isSkip());
        $this->assertTrue($this->_items[2]->isSkip());
    }
}
