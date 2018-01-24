<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Exception\LocalizedException;

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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $searchHelper;

    /**
     * @var integer
     */
    private $batchSize;

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory $indexerHandlerFactory
     * @param \Magento\Framework\Indexer\IndexStructureInterface $indexStructure
     * @param \Magento\Framework\Indexer\SaveHandler\Batch $batch
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     * @param \Doofinder\Feed\Helper\Search $searchHelper
     * @param array $data
     * @param integer $batchSize
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.LongVariable)
     * @codingStandardsIgnoreStart
     * Ignore MEQP2.Classes.ConstructorOperations.CustomOperationsFound
     */
    public function __construct(
        \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory $indexerHandlerFactory,
        \Magento\Framework\Indexer\IndexStructureInterface $indexStructure,
        \Magento\Framework\Indexer\SaveHandler\Batch $batch,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Helper\Search $searchHelper,
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
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->feedConfig = $feedConfig;
        $this->generatorFactory = $generatorFactory;
        $this->searchHelper = $searchHelper;
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
     * @return boolean
     */
    public function isAvailable()
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
            $this->performAction(array_keys($documents), $dimensions, 'update');
        }
    }

    /**
     * @param  array $documents
     * @param  \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return void
     */
    private function dropDocuments(array $documents, array $dimensions)
    {
        if (!empty($documents)) {
            $this->performAction(array_keys($documents), $dimensions, 'delete');
        }
    }

    /**
     * Perform action
     *   - update - Run generator with AtomicUpdater
     *   - delete - Delete items from index
     *
     * @param  int[] $ids
     * @param  \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param  string $action
     * @return void
     * @throws LocalizedException Unknown action.
     */
    private function performAction(array $ids, array $dimensions, $action)
    {
        $originalStoreCode = $this->storeConfig->getStoreCode();

        $storeId = $this->searchHelper->getStoreIdFromDimensions($dimensions);
        $this->storeManager->setCurrentStore($storeId);

        if ($action == 'update') {
            $feedConfig = $this->feedConfig->getLeanFeedConfig($storeId);

            // Add fixed product fetcher
            $feedConfig['data']['config']['fetchers']['Product\Fixed'] = [
                'products' => $this->getProducts($ids),
            ];

            // Add atomic update processor
            $feedConfig['data']['config']['processors']['AtomicUpdater'] = [];

            $generator = $this->generatorFactory->create($feedConfig);
            $generator->run();
        } elseif ($action == 'delete') {
            $this->searchHelper->deleteDoofinderItems(array_map(function ($id) {
                return ['id' => $id];
            }, $ids));
        } else {
            throw new LocalizedException(__('Unknown Doofinder indexer action'));
        }

        $this->storeManager->setCurrentStore($originalStoreCode);
    }

    /**
     * Get products
     *
     * @param int[] $ids
     * @return \Magento\Catalog\Model\Product[]
     */
    private function getProducts(array $ids)
    {
        $builder = $this->searchCriteriaBuilder;
        $builder->addFilter('entity_id', $ids, 'in');

        $results = $this->productRepository->getList(
            $builder->create()
        );

        return $results->getItems();
    }
}
