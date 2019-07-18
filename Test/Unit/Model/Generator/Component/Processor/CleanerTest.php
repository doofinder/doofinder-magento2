<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner
 */
class CleanerTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
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
     * @var array
     */
    private $data = [
        'title' => ' Sample  product ',
        'description' => "Brand <strong>new</strong> product.<br />Check it out. \xa0\xa1 <!",
    ];

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->item = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Item::class)
            ->setMethods(['getData', 'getDescription'])
            ->setConstructorArgs(['data' => $this->data])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner::class
        );
    }

    /**
     * Test process() method
     *
     * @return void
     */
    public function testProcess()
    {
        $this->item
            ->method('getData')
            ->withConsecutive([], ['title'])
            ->willReturnOnConsecutiveCalls($this->data, 'Sample product');

        $this->item
            ->method('getDescription')
            ->willReturn('Brand new product. Check it out.');

        $this->model->process([$this->item]);

        $this->assertEquals('Sample product', $this->item->getData('title'));
        $this->assertEquals('Brand new product. Check it out.', $this->item->getDescription());
    }
}
