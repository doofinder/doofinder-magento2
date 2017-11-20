<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;

class IndexerHandler implements IndexerInterface
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexerHandler
     */
    private $_indexerHandler;

    /**
     * \Magento\Framework\Indexer\IndexStructureInterface
     */
    private $_indexStructure;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch
     */
    private $_batch;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $_productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $_feedConfig;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $_searchHelper;

    /**
     * @var int
     */
    private $_batchSize;

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
     * @param int $batchSize = 100
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        $this->_indexerHandler = $indexerHandlerFactory->create([
            'data' => $data,
            'batchSize' => $batchSize
        ]);
        $this->_indexStructure = $indexStructure;
        $this->_batch = $batch;
        $this->_productRepository = $productRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_storeManager = $storeManager;
        $this->_storeConfig = $storeConfig;
        $this->_feedConfig = $feedConfig;
        $this->_generatorFactory = $generatorFactory;
        $this->_searchHelper = $searchHelper;
        $this->_batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->_batch->getItems($documents, $this->_batchSize) as $batchDocuments) {
            $this->insertDocuments($batchDocuments, $dimensions);
            $this->_indexerHandler->saveIndex($dimensions, $this->createIterator($batchDocuments));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->_batch->getItems($documents, $this->_batchSize) as $batchDocuments) {
            $this->dropDocuments($batchDocuments, $dimensions);
            $this->_indexerHandler->deleteIndex($dimensions, $this->createIterator($batchDocuments));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->_indexerHandler->cleanIndex($dimensions);
    }

    /**
     * {@inheritdoc}
     * NOTICE Add hash id verification
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
     * @param array $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     */
    private function insertDocuments(array $documents, array $dimensions)
    {
        if (empty($documents)) {
            return;
        }

        $this->runGenerator(array_keys($documents), $dimensions, 'update');
    }

    /**
     * @param array $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     */
    private function dropDocuments(array $documents, array $dimensions)
    {
        if (empty($documents)) {
            return;
        }

        $this->runGenerator($documents, $dimensions, 'delete');
    }

    /**
     * Run generator
     *
     * @param int[] $ids
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param string $action
     */
    private function runGenerator(array $ids, array $dimensions, $action)
    {
        $originalStoreCode = $this->_storeConfig->getStoreCode();

        $storeId = $this->_searchHelper->getStoreIdFromDimensions($dimensions);
        $this->_storeManager->setCurrentStore($storeId);

        $feedConfig = $this->_feedConfig->getLeanFeedConfig($storeId);

        // Add fixed product fetcher
        $feedConfig['data']['config']['fetchers']['Product\Fixed'] = [
            'products' => $this->getProducts($ids),
        ];

        // Add atomic update processor
        $feedConfig['data']['config']['processors']['AtomicUpdater'] = [
            'action' => $action,
        ];

        if ($action == 'delete') {
            // We do not need fields other than id to delete items
            $map = $feedConfig['data']['config']['processors']['Mapper']['map'];
            $map = array_intersect_key($map, ['id' => '']);
            $feedConfig['data']['config']['processors']['Mapper']['map'] = $map;
        }

        $generator = $this->_generatorFactory->create($feedConfig);
        $generator->run();

        $this->_storeManager->setCurrentStore($originalStoreCode);
    }

    /**
     * Get products
     *
     * @param int[] $ids
     * @return \Magento\Catalog\Model\Product[]
     */
    private function getProducts(array $ids)
    {
        $builder = $this->_searchCriteriaBuilder;
        $builder->addFilter('entity_id', $ids, 'in');

        $results = $this->_productRepository->getList(
            $builder->create()
        );

        return $results->getItems();
    }
}
