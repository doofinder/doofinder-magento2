<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

class CleanerTest extends BaseTestCase
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
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

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

        $this->_model = $this->objectManager->getObject(
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
