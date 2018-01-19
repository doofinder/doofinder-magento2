<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Xml
 */
class XmlTest extends BaseTestCase
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

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );

        $this->xmlWriter = $this->getMock(
            \Sabre\Xml\Writer::class,
            [],
            [],
            '',
            false
        );

        $this->xmlService = $this->getMock(
            \Sabre\Xml\Service::class,
            ['getWriter'],
            [],
            '',
            false
        );
        $this->xmlService->method('getWriter')->willReturn($this->xmlWriter);

        $this->helper = $this->getMock(
            \Doofinder\Feed\Helper\Data::class,
            ['getBaseUrl', 'getModuleVersion'],
            [],
            '',
            false
        );
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
