<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $_model;

    /**
     * @var \Sabre\Xml\Service
     */
    private $_xmlService;

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
            [],
            [],
            '',
            false
        );

        $this->_xmlService = $this->getMock(
            '\Sabre\Xml\Service',
            ['write'],
            [],
            '',
            false
        );

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Xml',
            [
                'xmlService' => $this->_xmlService
            ]
        );
    }

    /**
     * Test process
     */
    public function testProcess()
    {
        $this->_xmlService->expects($this->once())->method('write')
            ->with('feed', [$this->_item]);

        $this->_model->process([$this->_item]);
    }
}
