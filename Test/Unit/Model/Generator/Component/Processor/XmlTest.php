<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Xml
 */
class XmlTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $model;

    /**
     * @var \Sabre\Xml\Writer
     */
    private $xmlWriter;

    /**
     * @var \Sabre\Xml\Service
     */
    private $xmlService;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $helper;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->item = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->xmlWriter = $this->getMockBuilder(\Sabre\Xml\Writer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->xmlService = $this->getMockBuilder(\Sabre\Xml\Service::class)
            ->setMethods(['getWriter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->xmlService->method('getWriter')->willReturn($this->xmlWriter);

        $this->helper = $this->getMockBuilder(\Doofinder\Feed\Helper\Data::class)
            ->setMethods(['getBaseUrl', 'getModuleVersion'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->xmlService->method('getWriter')->willReturn($this->xmlWriter);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Component\Processor\Xml::class,
            [
                'xmlService' => $this->xmlService,
                'helper' => $this->helper,
            ]
        );
    }

    /**
     * Test process() method with open/output memory
     *
     * @return void
     */
    public function testProcess()
    {
        $this->xmlWriter->expects($this->once())->method('openMemory');
        $this->xmlWriter->expects($this->once())->method('outputMemory');

        $this->model->process([$this->item]);
    }
}
