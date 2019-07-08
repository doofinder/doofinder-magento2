<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Filter
 */
class FilterTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Filter
     */
    private $model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item[]
     */
    private $items;

    /**
     * Set up test
     *
     * @return void
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

        $this->items = [];

        foreach ($items as $item) {
            array_push(
                $this->items,
                $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Item::class)
                    ->setMethods(['getTitle'])
                    ->setConstructorArgs(['data' => $item])
                    ->getMock()
            );
        }

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Component\Processor\Filter::class
        );
    }

    /**
     * Test process() method
     *
     * @return void
     */
    public function testProcess()
    {
        $this->model->process($this->items);

        $this->assertFalse($this->items[0]->isSkip());
        $this->assertFalse($this->items[1]->isSkip());
        $this->assertTrue($this->items[2]->isSkip());
    }
}
