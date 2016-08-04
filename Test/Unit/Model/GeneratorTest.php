<?php

namespace Doofinder\Feed\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $_model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher
     */
    private $_fetcher;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\FetcherFactory
     */
    private $_fetcherFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Mapper
     */
    private $_mapperProcessor;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner
     */
    private $_cleanerProcessor;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $_xmlProcessor;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\ProcessorFactory
     */
    private $_processorFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );
        $this->_item->method('getData')
            ->willReturn([
                'name' => 'Sample product name',
                'description' => 'Sample product description'
            ]);

        $this->_fetcher = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\Fetcher',
            [],
            [],
            '',
            false
        );
        $this->_fetcher->method('fetch')
            ->willReturn([$this->_item]);

        $this->_fetcherFactory = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\FetcherFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_fetcherFactory->method('create')
            ->willReturn($this->_fetcher);

        $this->_mapperProcessor = $helper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Mapper'
        );
        $this->_cleanerProcessor = $helper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Cleaner'
        );
        $this->_xmlProcessor = $helper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Xml',
            [
                $this->getMock('\Sabre\Xml\Service')
            ]
        );

        $this->_processorFactory = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\ProcessorFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_processorFactory->expects($this->at(0))->method('create')
            ->with($this->anything(), 'Mapper')
            ->willReturn($this->_mapperProcessor);
        $this->_processorFactory->expects($this->at(1))->method('create')
            ->with($this->anything(), 'Cleaner')
            ->willReturn($this->_cleanerProcessor);
        $this->_processorFactory->expects($this->at(2))->method('create')
            ->with($this->anything(), 'Xml')
            ->willReturn($this->_xmlProcessor);

        $this->_model = $helper->getObject(
            'Doofinder\Feed\Model\Generator',
            [
                'fetcherFactory' => $this->_fetcherFactory,
                'processorFactory' => $this->_processorFactory,
                'data' => [
                    'config' => [
                        'fetchers' => [
                            'Product' => []
                        ],
                        'processors' => [
                            'Mapper' => [
                                'map' => [
                                    'title' => 'name'
                                ]
                            ],
                            'Cleaner' => [],
                            'Xml' => []
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * Test run
     */
    public function testRun()
    {
        $this->_model->run();
    }
}
