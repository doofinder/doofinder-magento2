<?php

namespace Doofinder\Feed\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Registry\IndexerScope;
use Doofinder\Feed\Model\ChangedProduct\Registration;

/**
 * Class IndexerHandler
 * The class responsible for indexing
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
     * @var Registration
     */
    private $registration;

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
     * @param Registration $registration
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
        Registration $registration,
        array $data = [],
        $batchSize = 100
    ) {
        $this->indexStructure = $indexStructure;
        $this->batch = $batch;
        $this->processor = $processor;
        $this->mapper = $mapper;
        $this->indexerHelper = $indexerHelper;
        $this->indexerScope = $indexerScope;
        $this->registration = $registration;
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
            foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
                $productIds = array_keys($batchDocuments);
                foreach ($productIds as $productId) {
                    $this->registration->registerUpdate(
                        $productId,
                        $this->indexerHelper->getStoreCodeFromDimensions($dimensions)
                    );
                }
            }
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
            foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
                $productIds = array_values($batchDocuments);
                foreach ($productIds as $productId) {
                    $this->registration->registerDelete(
                        $productId,
                        $this->indexerHelper->getStoreCodeFromDimensions($dimensions)
                    );
                }
            }
            return; // it's a Magento save index
        }

        $scopeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $docs = $this->mapper->get('delete')->map($batchDocuments, $scopeId);
            // phpcs:disable Ecg.Performance.Loop.ModelLSD
            $this->processor->delete($scopeId, $docs);
            // phpcs:enable
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
        $this->indexStructure->delete(null, $dimensions);
        $this->indexStructure->create(null, [], $dimensions);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $dimensions
     * @return boolean
     * @phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
     */
    public function isAvailable($dimensions = [])
    {
        // phpcs:enable
        return $this->indexerHelper->isAvailable($dimensions);
    }
}
