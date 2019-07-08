<?php

namespace Doofinder\Feed\Test\Unit\Model;

/**
 * Test class for \Doofinder\Feed\Model\Generator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\FetcherInterface
     */
    private $fetcher;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\FetcherFactory
     */
    private $fetcherFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Mapper
     */
    private $mapperProcessor;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner
     */
    private $cleanerProcessor;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $xmlProcessor;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\ProcessorFactory
     */
    private $processorFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * @var \Sabre\Xml\Writer
     */
    private $xmlWriter;

    /**
     * @var \Sabre\Xml\Service
     */
    private $xmlService;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Set up test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

        $this->xmlWriter = $this->getMockBuilder(\Sabre\Xml\Writer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->xmlService = $this->getMockBuilder(\Sabre\Xml\Service::class)
            ->setMethods(['getWriter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->xmlService->method('getWriter')->willReturn($this->xmlWriter);

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->item = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->item->method('getData')
            ->willReturn([
                'name' => 'Sample product name',
                'description' => 'Sample product description'
            ]);
        $this->item->method('getContext')->willReturn($this->product);
        $this->item->method('isSkip')->will($this->onConsecutiveCalls(
            false,
            true,
            false,
            false,
            false,
            true
        ));

        $this->fetcher = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Component\FetcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcher->method('fetch')
            ->willReturn([$this->item, $this->item]);

        $this->fetcherFactory = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Component\FetcherFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcherFactory->method('create')
            ->willReturn($this->fetcher);

        $this->mapperProcessor = $this->getMockBuilder(
            \Doofinder\Feed\Model\Generator\Component\Processor\Mapper::class
        )->disableOriginalConstructor()
        ->getMock();
        $this->mapperProcessor->expects($this->once())->method('process')->with([$this->item]);
        $this->cleanerProcessor = $this->getMockBuilder(
            \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner::class
        )->disableOriginalConstructor()
        ->getMock();
        $this->cleanerProcessor->expects($this->once())->method('process')->with([$this->item, $this->item]);
        $this->xmlProcessor = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Component\Processor\Xml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->xmlProcessor->expects($this->once())->method('process')->with([$this->item]);

        $this->processorFactory = $this->getMockBuilder(
            \Doofinder\Feed\Model\Generator\Component\ProcessorFactory::class
        )->setMethods(['create'])
        ->disableOriginalConstructor()
        ->getMock();
        $this->processorFactory->expects($this->at(0))->method('create')
            ->with($this->anything(), 'Mapper')
            ->willReturn($this->mapperProcessor);
        $this->processorFactory->expects($this->at(1))->method('create')
            ->with($this->anything(), 'Cleaner')
            ->willReturn($this->cleanerProcessor);
        $this->processorFactory->expects($this->at(2))->method('create')
            ->with($this->anything(), 'Xml')
            ->willReturn($this->xmlProcessor);

        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator::class,
            [
                'fetcherFactory' => $this->fetcherFactory,
                'processorFactory' => $this->processorFactory,
                'eventManager' => $this->eventManager,
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
     * Test run() method
     *
     * @return void
     */
    public function testRun()
    {
        $this->eventManager->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                ['doofinder_feed_generator_initialized'],
                ['doofinder_feed_generator_items_fetched'],
                ['doofinder_feed_generator_items_processed']
            );

        $this->model->run();
    }
}
