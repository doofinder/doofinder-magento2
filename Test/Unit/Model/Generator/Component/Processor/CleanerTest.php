<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

class CleanerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner
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
                    'title' => ' Sample  product ',
                    'description' => "Brand <strong>new</strong> product.<br />Check it out. \xa0\xa1 <!",
                ]
            ]
        );

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Cleaner',
            [
            ]
        );
    }

    /**
     * Test process
     */
    public function testProcess()
    {
        $this->_model->process([$this->_item]);

        $this->assertEquals('Sample product', $this->_item->getData('title'));
        $this->assertEquals('Brand new product. Check it out.', $this->_item->getDescription());
    }
}
