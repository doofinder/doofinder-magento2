<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;

/**
 * Indexer handler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerHandler implements IndexerInterface
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexerHandler
     */
    private $indexerHandler;

    /**
     * @var \Magento\Framework\Indexer\IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch
     */
    private $batch;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $searchHelper;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var integer
     */
    private $batchSize;

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory $indexerHandlerFactory
     * @param \Magento\Framework\Indexer\IndexStructureInterface $indexStructure
     * @param \Magento\Framework\Indexer\SaveHandler\Batch $batch
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Doofinder\Feed\Helper\Search $searchHelper
     * @param Processor $processor
     * @param array $data
     * @param integer $batchSize
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codingStandardsIgnoreStart
     * Ignore MEQP2.Classes.ConstructorOperations.CustomOperationsFound
     */
    public function __construct(
        \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory $indexerHandlerFactory,
        \Magento\Framework\Indexer\IndexStructureInterface $indexStructure,
        \Magento\Framework\Indexer\SaveHandler\Batch $batch,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Doofinder\Feed\Helper\Search $searchHelper,
        Processor $processor,
        array $data,
        $batchSize = 100
    ) {
    // @codingStandardsIgnoreEnd
        $this->indexerHandler = $indexerHandlerFactory->create([
            'data' => $data,
            'batchSize' => $batchSize
        ]);
        $this->indexStructure = $indexStructure;
        $this->batch = $batch;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->searchHelper = $searchHelper;
        $this->processor = $processor;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed $dimensions
     * @param  \Traversable $documents
     * @return void
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocuments($batchDocuments, $dimensions);
            $this->indexerHandler->saveIndex($dimensions, $this->createIterator($batchDocuments));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed $dimensions
     * @param  \Traversable $documents
     * @return void
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->dropDocuments($batchDocuments, $dimensions);
            $this->indexerHandler->deleteIndex($dimensions, $this->createIterator($batchDocuments));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed $dimensions
     * @return void
     */
    public function cleanIndex($dimensions)
    {
        $this->indexerHandler->cleanIndex($dimensions);
    }

    /**
     * {@inheritdoc}
     *
     * NOTICE: Add hash id verification
     * @param array $dimensions
     * @return boolean
     */
    // @codingStandardsIgnoreLine - do not hint array
    public function isAvailable($dimensions = [])
    {
        return true;
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

    /**
     * @param  array $documents
     * @param  \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return void
     */
    private function insertDocuments(array $documents, array $dimensions)
    {
        if (!empty($documents)) {
            $storeId = $this->searchHelper->getStoreIdFromDimensions($dimensions);
            $this->processor->update($storeId, $this->getProducts(array_keys($documents), $storeId));
        }
    }

    /**
     * @param  array $documents
     * @param  \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return void
     */
    private function dropDocuments(array $documents, array $dimensions)
    {
        $ids = $this->getDocumentsToDrop($documents);
        if (!empty($ids)) {
            $storeId = $this->searchHelper->getStoreIdFromDimensions($dimensions);
            $this->processor->delete($storeId, array_values($ids));
        }
    }

    /**
     * Retrieve products that should be removed from API
     * @param array $documents
     * @return array
     */
    private function getDocumentsToDrop(array $documents)
    {
        $products = $this->getProducts(array_values($documents));
        $ids = [];
        foreach ($products as $product) {
            if (in_array($product->getVisibility(), $this->productVisibility->getVisibleInSearchIds())) {
                $ids[] = $product->getId(); // products that are visible in search
            }
        }
        return array_diff($documents, $ids);
    }

    /**
     * Get products
     *
     * @param integer[] $ids
     * @param integer|null $storeId
     * @return \Magento\Catalog\Model\Product[]
     */
    private function getProducts(array $ids, $storeId = null)
    {
        $collection = $this->productCollectionFactory->create();

        $collection->addAttributeToFilter('entity_id', ['in' => $ids])
            ->addAttributeToSelect('*')
            ->addUrlRewrite()
            ->addCategoryIds();

        if ($storeId) {
            $collection->setStoreId($storeId);
            $collection->addStoreFilter($storeId);
        }

        return $collection->getItems();
    }
}
