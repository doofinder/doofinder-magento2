<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner
 */
class CleanerTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner
     */
    private $model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            null,
            [
                'data' => [
                    'title' => ' Sample  product ',
                    'description' => "Brand <strong>new</strong> product.<br />Check it out. \xa0\xa1 <!",
                ]
            ]
        );

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner::class,
            [
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

        $this->assertEquals('Sample product', $this->item->getData('title'));
        $this->assertEquals('Brand new product. Check it out.', $this->item->getDescription());
    }
}
