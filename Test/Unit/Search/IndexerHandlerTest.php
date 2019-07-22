<?php

namespace Doofinder\Feed\Test\Unit\Search;

/**
 * Test class for \Doofinder\Feed\Search\IndexerHandler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerHandlerTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
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
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

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

        // @codingStandardsIgnoreStart
        $this->documents = new \ArrayObject([1234 => ['sample' => 'item']]);
        // @codingStandardsIgnoreEnd

        $this->batch = $this->getMockBuilder(\Magento\Framework\Indexer\SaveHandler\Batch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->productCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->productCollectionFactory->method('create')->willReturn($this->productCollection);
        $this->productCollection->method('addAttributeToFilter')->willReturnSelf();
        $this->productCollection->method('addAttributeToSelect')->willReturnSelf();
        $this->productCollection->method('addUrlRewrite')->willReturnSelf();
        $this->productCollection->method('getItems')->willReturn([$this->product]);

        $this->productVisibility = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Visibility::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->productVisibility->method('getVisibleInSearchIds')
            ->willReturn(['3, 4']);

        $this->dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dimension->method('getName')->willReturn('scope');
        $this->dimension->method('getValue')->willReturn('sample');

        $this->searchHelper = $this->getMockBuilder(\Doofinder\Feed\Helper\Search::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchHelper->method('getStoreIdFromDimensions')
            ->with([$this->dimension])->willReturn('sample');

        $this->indexStructure = $this->getMockBuilder(\Magento\Framework\Indexer\IndexStructureInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerHandler = $this->getMockBuilder(\Magento\CatalogSearch\Model\Indexer\IndexerHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerHandlerFactory = $this->getMockBuilder(
            \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory::class
        )->disableOriginalConstructor()
        ->getMock();
        $this->indexerHandlerFactory->method('create')
            ->with([
                'data' => [
                    'indexer_id' => 'sample-indexer',
                    'fieldsets' => [],
                ],
                'batchSize' => 100,
            ])
            ->willReturn($this->indexerHandler);

        $this->processor = $this->getMockBuilder(\Doofinder\Feed\Search\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexer = $this->objectManager->getObject(
            \Doofinder\Feed\Search\IndexerHandler::class,
            [
                'batch' => $this->batch,
                'productCollectionFactory' => $this->productCollectionFactory,
                'productVisibility' => $this->productVisibility,
                'searchHelper' => $this->searchHelper,
                'indexStructure' => $this->indexStructure,
                'indexerHandlerFactory' => $this->indexerHandlerFactory,
                'processor' => $this->processor,
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

        $this->processor->expects($this->once())->method('update')
            ->with('sample', [$this->product]);

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
        $this->documents = new \ArrayObject([1234]); // @codingStandardsIgnoreLine

        $batch = $this->documents->getArrayCopy();
        $this->batch->expects($this->at(0))->method('getItems')
            ->with($this->documents, 100)->willReturn([$batch]);

        $this->processor->expects($this->once())
            ->method('delete')
            ->with('sample', [1234]);

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
