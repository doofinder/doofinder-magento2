<?php

namespace Doofinder\Feed\Model\ChangedProduct;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Model\Indexer\IndexerHandler;
use Doofinder\Feed\Registry\IndexerScope;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Area;

/**
 * Class Processor
 * The class responsible for executing Indexer on Changed Products
 */
class Processor
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var Processor\CollectionProvider
     */
    private $collectionProvider;

    /**
     * @var Processor\DocumentsProvider
     */
    private $documentsProvider;

    /**
     * @var IndexerHelper
     */
    private $indexerHelper;

    /**
     * @var IndexerHandler
     */
    private $indexerHandler;

    /**
     * @var IndexerScope
     */
    private $indexerScope;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * PerformDelayedUpdates constructor.
     * @param StoreConfig $storeConfig
     * @param Processor\CollectionProvider $collectionProvider
     * @param Processor\DocumentsProvider $documentsProvider
     * @param IndexerHelper $indexerHelper
     * @param IndexerHandler $indexerHandler
     * @param IndexerScope $indexerScope
     * @param Emulation $appEmulation
     */
    public function __construct(
        StoreConfig $storeConfig,
        Processor\CollectionProvider $collectionProvider,
        Processor\DocumentsProvider $documentsProvider,
        IndexerHelper $indexerHelper,
        IndexerHandler $indexerHandler,
        IndexerScope $indexerScope,
        Emulation $appEmulation
    ) {
        $this->storeConfig = $storeConfig;
        $this->collectionProvider = $collectionProvider;
        $this->documentsProvider = $documentsProvider;
        $this->indexerHelper = $indexerHelper;
        $this->indexerHandler = $indexerHandler;
        $this->indexerScope = $indexerScope;
        $this->appEmulation = $appEmulation;
    }

    /**
     * Execute Delayed Updates
     * @return void
     */
    public function execute()
    {
        $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_DELAYED);
        foreach ($this->storeConfig->getStoreCodes() as $storeCode) {
            $storeId = $this->storeConfig->getStoreViewIdByStoreCode($storeCode);
            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
            $dimensions = $this->getDimensions($storeId);

            $this->processDelete($dimensions);
            $this->processUpdate($dimensions);

            $this->appEmulation->stopEnvironmentEmulation();
        }
        $this->indexerScope->setIndexerScope(null);
    }

    /**
     * @param mixed $dimensions
     * @return void
     */
    public function processDelete($dimensions)
    {
        $storeCode = $this->indexerHelper->getStoreCodeFromDimensions($dimensions);
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_DELETE, $storeCode);

        $this->indexerHandler->deleteIndex(
            $dimensions,
            $this->documentsProvider->getDeleted($collection)
        );
        $collection->walk('delete');
    }

    /**
     * @param mixed $dimensions
     * @return void
     */
    public function processUpdate($dimensions)
    {
        $storeCode = $this->indexerHelper->getStoreCodeFromDimensions($dimensions);
        $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
        $collection = $this->collectionProvider->get(ChangedProductResource::OPERATION_UPDATE, $storeCode);

        $this->indexerHandler->saveIndex(
            $dimensions,
            $this->documentsProvider->getUpdated($collection, $storeId)
        );
        $collection->walk('delete');
    }

    /**
     * @param integer $storeId
     * @return array
     */
    private function getDimensions($storeId)
    {
        return [$this->indexerHelper->getDimensions($storeId)];
    }
}
