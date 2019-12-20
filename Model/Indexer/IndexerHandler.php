<?php

namespace Doofinder\Feed\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Registry\IndexerScope;

/**
 * Indexer handler
 */
class IndexerHandler implements IndexerInterface
{
    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var IndexerHelper
     */
    private $indexerHelper;

    /**
     * @var IndexerScope
     */
    private $indexerScope;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var Data\Mapper
     */
    private $mapper;

    /**
     * @var array
     */
    private $data;

    /**
     * @var integer
     */
    private $batchSize;

    /**
     * IndexerHandler constructor.
     * @param IndexStructureInterface $indexStructure
     * @param Batch $batch
     * @param Processor $processor
     * @param Data\Mapper $mapper
     * @param IndexerHelper $indexerHelper
     * @param IndexerScope $indexerScope
     * @param array $data
     * @param integer $batchSize
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        Batch $batch,
        Processor $processor,
        Data\Mapper $mapper,
        IndexerHelper $indexerHelper,
        IndexerScope $indexerScope,
        array $data = [],
        $batchSize = 100
    ) {
        $this->indexStructure = $indexStructure;
        $this->batch = $batch;
        $this->processor = $processor;
        $this->mapper = $mapper;
        $this->indexerHelper = $indexerHelper;
        $this->indexerScope = $indexerScope;
        $this->data = $data;
        $this->batchSize = $batchSize;
    }

    /**
     * Check if indexer mode is Index on Save
     * @return boolean
     */
    private function canProceed()
    {
        return $this->indexerScope->getIndexerScope() == $this->indexerScope::SCOPE_FULL
            || $this->indexerScope->getIndexerScope() == $this->indexerScope::SCOPE_DELAYED;
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
        if (!$this->canProceed()) {
            return; // it's a Magento save index
        }

        $scopeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $docs = $this->mapper->get('update')->map($batchDocuments, $scopeId);
            $this->processor->update($scopeId, $docs);
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
        if (!$this->canProceed()) {
            return; // it's a Magento save index
        }
        $scopeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $docs = $this->mapper->get('delete')->map($batchDocuments, $scopeId);
            $this->processor->delete($scopeId, $docs); // @codingStandardsIgnoreLine - it's not ModelLSD
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
        $this->indexStructure->delete(null, [], $dimensions);
        $this->indexStructure->create(null, [], $dimensions);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $dimensions
     * @return boolean
     */
    // @codingStandardsIgnoreLine - do not hint array
    public function isAvailable($dimensions = [])
    {
        return $this->indexerHelper->isAvailable($dimensions);
    }
}
