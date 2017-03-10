<?php

namespace Doofinder\Feed\Search;

class IndexerHandler extends \Magento\CatalogSearch\Model\Indexer\IndexerHandler
{
    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch
     */
    protected $_batch;

    /**
     * @var \Magento\Framework\Indexer\IndexStructureInterface
     */
    protected $_indexStructure;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    protected $_feedConfig;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    protected $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    protected $_searchHelper;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var int
     */
    protected $_batchSize;

    /**
     * @param \Magento\Framework\Indexer\IndexStructureInterface $indexStructure
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Indexer\SaveHandler\Batch $batch
     * @param \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     * @param \Doofinder\Feed\Helper\Search
     * @param array $data
     * @param int $batchSize = 100
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexStructureInterface $indexStructure,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Indexer\SaveHandler\Batch $batch,
        \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Helper\Search $searchHelper,
        array $data,
        $batchSize = 100
    ) {
        parent::__construct(
            $indexStructure,
            $resource,
            $eavConfig,
            $batch,
            $indexScopeResolver,
            $data,
            $batchSize
        );

        $this->_batch = $batch;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_storeConfig = $storeConfig;
        $this->_feedConfig = $feedConfig;
        $this->_generatorFactory = $generatorFactory;
        $this->_searchHelper = $searchHelper;
        $this->_indexStructure = $indexStructure;
        $this->_data = $data;
        $this->_batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->_batch->getItems(clone($documents), $this->_batchSize) as $batchDocuments) {
            $this->insertDocuments($batchDocuments, $dimensions);
        }

        parent::saveIndex($dimensions, $documents);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->_batch->getItems(clone($documents), $this->_batchSize) as $batchDocuments) {
            $this->dropDocuments($batchDocuments, $dimensions);
        }

        parent::deleteIndex($dimensions, $documents);
    }

    /**
     * {@inheritdoc}
     * @todo Add hash id verification
     */
    public function isAvailable()
    {
        return true;
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
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['in' => $ids]);
        $collection->addStoreFilter();
        return $collection->getItems();
    }

    /**
     * @return string
     */
    private function getIndexName()
    {
        return $this->_data['indexer_id'];
    }
}
