<?php

namespace Doofinder\Feed\Test\Unit\Search;

/**
 * Test class for \Doofinder\Feed\Search\Processor
 */
class ProcessorTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $generator;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $searchHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Doofinder\Feed\Search\Processor
     */
    private $processor;

    /**
     * Set up test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

        $this->generator = $this->getMockBuilder(\Doofinder\Feed\Model\Generator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generatorFactory = $this->getMockBuilder(\Doofinder\Feed\Model\GeneratorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->feedConfig = $this->getMockBuilder(\Doofinder\Feed\Helper\FeedConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedConfig->method('getLeanFeedConfig')->willReturn([
            'data' => [
                'config' => [
                    'processors' => [
                        'Mapper' => [
                            'map' => [],
                        ],
                    ],
                ],
            ],
        ]);

        $this->searchHelper = $this->getMockBuilder(\Doofinder\Feed\Helper\Search::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = $this->objectManager->getObject(
            \Doofinder\Feed\Search\Processor::class,
            [
                'generatorFactory' => $this->generatorFactory,
                'feedConfig' => $this->feedConfig,
                'searchHelper' => $this->searchHelper,
            ]
        );
    }

    /**
     * Test update() method
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->generator->expects($this->once())->method('run');

        $this->generatorFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'config' => [
                        'fetchers' => [
                            'Product\Fixed' => [
                                'products' => [$this->product],
                            ],
                        ],
                        'processors' => [
                            'AtomicUpdater' => [],
                            'Mapper' => [
                                'map' => [],
                            ],
                        ],
                    ],
                ],
            ])
            ->willReturn($this->generator);

        $this->processor->update('sample', [$this->product]);
    }

    /**
     * Test delete() method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->searchHelper->expects($this->once())
            ->method('deleteDoofinderItems')
            ->with([
                ['id' => 1234],
            ]);

        $this->processor->delete('sample', [1234]);
    }
}
