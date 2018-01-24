<?php

namespace Doofinder\Feed\Test\Unit\Model;

use Doofinder\Feed\Test\Unit\BaseTestCase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Doofinder\Feed\Model\Generator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends BaseTestCase
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

        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [],
            [],
            '',
            false
        );

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );
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

        $this->fetcher = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\FetcherInterface::class,
            [],
            [],
            '',
            false
        );
        $this->fetcher->method('fetch')
            ->willReturn([$this->item, $this->item]);

        $this->fetcherFactory = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\FetcherFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->fetcherFactory->method('create')
            ->willReturn($this->fetcher);

        $this->mapperProcessor = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\Processor\Mapper::class,
            [],
            [],
            '',
            false
        );
        $this->mapperProcessor->expects($this->once())->method('process')->with([$this->item]);
        $this->cleanerProcessor = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\Processor\Cleaner::class,
            [],
            [],
            '',
            false
        );
        $this->cleanerProcessor->expects($this->once())->method('process')->with([$this->item, $this->item]);
        $this->xmlProcessor = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\Processor\Xml::class,
            [],
            [],
            '',
            false
        );
        $this->xmlProcessor->expects($this->once())->method('process')->with([$this->item]);

        $this->processorFactory = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\ProcessorFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->processorFactory->expects($this->at(0))->method('create')
            ->with($this->anything(), 'Mapper')
            ->willReturn($this->mapperProcessor);
        $this->processorFactory->expects($this->at(1))->method('create')
            ->with($this->anything(), 'Cleaner')
            ->willReturn($this->cleanerProcessor);
        $this->processorFactory->expects($this->at(2))->method('create')
            ->with($this->anything(), 'Xml')
            ->willReturn($this->xmlProcessor);

        $this->eventManager = $this->getMock(
            \Magento\Framework\Event\ManagerInterface::class,
            ['dispatch'],
            [],
            '',
            false
        );

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
