<?php

namespace Doofinder\Feed\Test\Unit\Search;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Search\IndexerHandler
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerHandlerTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Search\IndexerHandler
     */
    private $indexer;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexerHandler
     */
    private $indexerHandler;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch
     */
    private $batch;

    /**
     * @var \Traversable
     */
    private $documents;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $generator;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    private $productSearchResults;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $searchHelper;

    /**
     * @var \Magento\Framework\Search\Request\Dimension
     */
    private $dimension;

    /**
     * @var \Magento\Framework\Indexer\IndexStructureInterface
     */
    private $indexStructure;

    /**
     * Set up test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

        // @codingStandardsIgnoreStart
        $this->documents = new \ArrayObject([1234 => ['sample' => 'item']]);
        // @codingStandardsIgnoreEnd

        $this->batch = $this->getMock(
            \Magento\Framework\Indexer\SaveHandler\Batch::class,
            [],
            [],
            '',
            false
        );

        $this->generator = $this->getMock(
            \Doofinder\Feed\Model\Generator::class,
            [],
            [],
            '',
            false
        );

        $this->generatorFactory = $this->getMock(
            \Doofinder\Feed\Model\GeneratorFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [],
            [],
            '',
            false
        );

        $this->productSearchResults = $this->getMock(
            \Magento\Catalog\Api\Data\ProductSearchResultsInterface::class,
            [],
            [],
            '',
            false
        );
        $this->productSearchResults->method('getItems')
            ->willReturn([$this->product]);

        $this->productRepository = $this->getMock(
            \Magento\Catalog\Model\ProductRepository::class,
            [],
            [],
            '',
            false
        );
        $this->productRepository->method('getList')
            ->willReturn($this->productSearchResults);

        $this->searchCriteria = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaInterface::class,
            [],
            [],
            '',
            false
        );

        $this->searchCriteriaBuilder = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->searchCriteriaBuilder->method('create')
            ->willReturn($this->searchCriteria);

        $this->feedConfig = $this->getMock(
            \Doofinder\Feed\Helper\FeedConfig::class,
            [],
            [],
            '',
            false
        );
        $this->feedConfig->method('getLeanFeedConfig')->with('sample')->willReturn([
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

        $this->dimension = $this->getMock(
            \Magento\Framework\Search\Request\Dimension::class,
            [],
            [],
            '',
            false
        );
        $this->dimension->method('getName')->willReturn('scope');
        $this->dimension->method('getValue')->willReturn('sample');

        $this->searchHelper = $this->getMock(
            \Doofinder\Feed\Helper\Search::class,
            [],
            [],
            '',
            false
        );
        $this->searchHelper->method('getStoreIdFromDimensions')
            ->with([$this->dimension])->willReturn('sample');

        $this->indexStructure = $this->getMock(
            \Magento\Framework\Indexer\IndexStructureInterface::class,
            [],
            [],
            '',
            false
        );

        $this->indexerHandler = $this->getMock(
            \Magento\CatalogSearch\Model\Indexer\IndexerHandler::class,
            [],
            [],
            '',
            false
        );

        $this->indexerHandlerFactory = $this->getMock(
            \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory::class,
            [],
            [],
            '',
            false
        );
        $this->indexerHandlerFactory->method('create')
            ->with([
                'data' => [
                    'indexer_id' => 'sample-indexer',
                    'fieldsets' => [],
                ],
                'batchSize' => 100,
            ])
            ->willReturn($this->indexerHandler);

        $this->indexer = $this->objectManager->getObject(
            \Doofinder\Feed\Search\IndexerHandler::class,
            [
                'batch' => $this->batch,
                'generatorFactory' => $this->generatorFactory,
                'productRepository' => $this->productRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'feedConfig' => $this->feedConfig,
                'searchHelper' => $this->searchHelper,
                'indexStructure' => $this->indexStructure,
                'indexerHandlerFactory' => $this->indexerHandlerFactory,
                'data' => [
                    'indexer_id' => 'sample-indexer',
                    'fieldsets' => [],
                ],
                'batchSize' => 100,
            ]
        );
    }

    /**
     * Test saveIndex() method
     *
     * @return void
     */
    public function testSaveIndex()
    {
        $batch = $this->documents->getArrayCopy();
        $this->batch->expects($this->at(0))->method('getItems')
            ->with($this->documents, 100)->willReturn([$batch]);

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

        $this->indexerHandler->expects($this->once())->method('saveIndex')
            ->with([$this->dimension], $this->createIterator($batch));

        $this->indexer->saveIndex([$this->dimension], $this->documents);
    }

    /**
     * Test deleteIndex() method
     *
     * @return void
     */
    public function testDeleteIndex()
    {
        $batch = $this->documents->getArrayCopy();
        $this->batch->expects($this->at(0))->method('getItems')
            ->with($this->documents, 100)->willReturn([$batch]);

        $this->searchHelper->expects($this->once())
            ->method('deleteDoofinderItems')
            ->with([
                ['id' => 1234],
            ]);

        $this->indexerHandler->expects($this->once())->method('deleteIndex')
            ->with([$this->dimension], $this->createIterator($batch));

        $this->indexer->deleteIndex([$this->dimension], $this->documents);
    }

    /**
     * Test cleanIndex() method
     *
     * @return void
     */
    public function testCleanIndex()
    {
        $this->indexerHandler->expects($this->once())->method('cleanIndex')
            ->with([$this->dimension]);

        $this->indexer->cleanIndex([$this->dimension]);
    }

    /**
     * Test isAvailable() method
     *
     * @return void
     */
    public function testIsAvailable()
    {
        $this->assertTrue($this->indexer->isAvailable());
    }

    /**
     * Create iterator for array
     *
     * @param array $arr
     * @return \ArrayIterator
     */
    private function createIterator(array $arr)
    {
        // @codingStandardsIgnoreLine
        return new \ArrayIterator($arr);
    }
}
