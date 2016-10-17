<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $_model;

    /**
     * @var \Sabre\Xml\Writer
     */
    private $_xmlWriter;

    /**
     * @var \Sabre\Xml\Service
     */
    private $_xmlService;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $_helper;

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

        $this->_xmlWriter = $this->getMock(
            '\Sabre\Xml\Writer',
            [],
            [],
            '',
            false
        );

        $this->_xmlService = $this->getMock(
            '\Sabre\Xml\Service',
            ['getWriter'],
            [],
            '',
            false
        );
        $this->_xmlService->method('getWriter')->willReturn($this->_xmlWriter);

        $this->_helper = $this->getMock(
            '\Doofinder\Feed\Helper\Data',
            ['getBaseUrl', 'getModuleVersion'],
            [],
            '',
            false
        );
        $this->_xmlService->method('getWriter')->willReturn($this->_xmlWriter);

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Xml',
            [
                'xmlService' => $this->_xmlService,
                'helper' => $this->_helper,
            ]
        );
    }

    /**
     * Test process open/output
     */
    public function testProcess()
    {
        $this->_xmlWriter->expects($this->once())->method('openMemory');
        $this->_xmlWriter->expects($this->once())->method('outputMemory');

        $this->_model->process([$this->_item]);
    }
}
