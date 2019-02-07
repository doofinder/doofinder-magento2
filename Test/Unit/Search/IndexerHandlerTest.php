<?php

namespace Doofinder\Feed\Test\Unit\Search;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Search\IndexerHandler
 *
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

        $this->batch = $this->getMock(
            \Magento\Framework\Indexer\SaveHandler\Batch::class,
            [],
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

        $this->productCollectionFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            [],
            [],
            '',
            false
        );

        $this->productCollection = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->productCollectionFactory->method('create')->willReturn($this->productCollection);
        $this->productCollection->method('addAttributeToFilter')->willReturnSelf();
        $this->productCollection->method('addAttributeToSelect')->willReturnSelf();
        $this->productCollection->method('addUrlRewrite')->willReturnSelf();
        $this->productCollection->method('getItems')->willReturn([$this->product]);

        $this->productVisibility = $this->getMock(
            \Magento\Catalog\Model\Product\Visibility::class,
            [],
            [],
            '',
            false
        );

        $this->productVisibility->method('getVisibleInSearchIds')
            ->willReturn(['3, 4']);

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

        $this->processor = $this->getMock(
            \Doofinder\Feed\Search\Processor::class,
            [],
            [],
            '',
            false
        );

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
