<?php

namespace Doofinder\Feed\Test\Unit\Model;

use Doofinder\Feed\Test\Unit\BaseTestCase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GeneratorTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $_model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\FetcherInterface
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
     * @var \Magento\Catalog\Model\Product
     */
    private $_product;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * @var \Sabre\Xml\Writer
     */
    private $_xmlWriter;

    /**
     * @var \Sabre\Xml\Service
     */
    private $_xmlService;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * Prepares the environment before running a test.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

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

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

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
        $this->_item->method('getContext')->willReturn($this->_product);
        $this->_item->method('isSkip')->will($this->onConsecutiveCalls(
            false,
            true,
            false,
            false,
            false,
            true
        ));

        $this->_fetcher = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\FetcherInterface',
            [],
            [],
            '',
            false
        );
        $this->_fetcher->method('fetch')
            ->willReturn([$this->_item, $this->_item]);

        $this->_fetcherFactory = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\FetcherFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_fetcherFactory->method('create')
            ->willReturn($this->_fetcher);

        $this->_mapperProcessor = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Mapper',
            [],
            [],
            '',
            false
        );
        $this->_mapperProcessor->expects($this->once())->method('process')->with([$this->_item]);
        $this->_cleanerProcessor = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Cleaner',
            [],
            [],
            '',
            false
        );
        $this->_cleanerProcessor->expects($this->once())->method('process')->with([$this->_item, $this->_item]);
        $this->_xmlProcessor = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Xml',
            [],
            [],
            '',
            false
        );
        $this->_xmlProcessor->expects($this->once())->method('process')->with([$this->_item]);

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

        $this->_eventManager = $this->getMock(
            '\Magento\Framework\Event\ManagerInterface',
            ['dispatch'],
            [],
            '',
            false
        );

        $this->_model = $this->objectManager->getObject(
            'Doofinder\Feed\Model\Generator',
            [
                'fetcherFactory' => $this->_fetcherFactory,
                'processorFactory' => $this->_processorFactory,
                'eventManager' => $this->_eventManager,
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
        $this->_eventManager->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                ['doofinder_feed_generator_initialized'],
                ['doofinder_feed_generator_items_fetched'],
                ['doofinder_feed_generator_items_processed']
            );

        $this->_model->run();
    }
}
