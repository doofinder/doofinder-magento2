<?php

declare(strict_types=1);

namespace Doofinder\Feed\Cron;

use Doofinder\Feed\Helper\Indice as IndiceHelper;
use Doofinder\Feed\Helper\Item as ItemHelper;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedProduct\DocumentsProvider;
use Doofinder\Feed\Model\Indexer\Data\Mapper;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\CollectionFactory as ChangedProductCollectionFactory;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Psr\Log\LoggerInterface;

class Processor
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var ChangedProductCollectionFactory
     */
    private $changedProductCollectionFactory;

    /**
     * @var DocumentsProvider
     */
    private $documentsProvider;

    /**
     * @var ItemHelper
     */
    private $itemHelper;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var integer
     */
    private $batchSize;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StoreConfig $storeConfig,
        ChangedProductCollectionFactory $changedProductCollectionFactory,
        DocumentsProvider $documentsProvider,
        ItemHelper $itemHelper,
        Mapper $mapper,
        Batch $batch,
        LoggerInterface $logger,
        $batchSize = 100
    ) {
        $this->storeConfig = $storeConfig;
        $this->changedProductCollectionFactory = $changedProductCollectionFactory;
        $this->documentsProvider = $documentsProvider;
        $this->itemHelper = $itemHelper;
        $this->mapper = $mapper;
        $this->batch = $batch;
        $this->batchSize = $batchSize;
        $this->logger = $logger;
    }

    /**
     * Update and delete items in search engine indice in bulk when "Update on save" option is enabled
     */
    public function execute()
    {
        if ($this->storeConfig->isUpdateOnSave()) {
            try {
                foreach ($this->storeConfig->getAllStores() as $store) {
                    $indice = IndiceHelper::MAGENTO_INDICE_NAME;
                    $this->createProducts($store, $indice);
                    $this->updateProducts($store, $indice);
                    $this->deleteProducts($store, $indice);
                }
            } catch (\Exception $e) {
                $this->logger->error('[Doofinder] Error processing updates: ' . $e->getMessage());
            }
        }
    }

    /**
     * Function to delete the products that have been stored into the data base as "to be created"
     */
    private function createProducts($store, $indice)
    {
        $collection = $this->changedProductCollectionFactory->create()->filterCreated((int)$store->getId());
        if ($collection->getSize()) {
            $created = $this->documentsProvider->getUpdated($collection, (int)$store->getId());
            foreach ($this->batch->getItems($created, $this->batchSize) as $batchDocuments) {
                $items = $this->mapper->get('update')->map($batchDocuments, (int)$store->getId());
                if (count($items)) {
                    try {
                        $this->logger->debug('[CreateInBulk]');
                        $this->logger->debug(\Zend_Json::encode($items));
                        $this->itemHelper->createItemsInBulk($items, $store, $indice);
                    } catch (\Exception $e) {
                        $this->logger->error(
                            sprintf(
                                '[Doofinder] There was an error while creating items in bulk: %s',
                                $e->getMessage()
                            )
                        );
                    }
                }
            }
            $collection->walk('delete');
        }
    }

    /**
     * Function to update the products that have been stored into the data base as "to be updated"
     */
    private function updateProducts($store, $indice)
    {
        $collection = $this->changedProductCollectionFactory->create()->filterUpdated((int)$store->getId());
        if ($collection->getSize()) {
            $updated = $this->documentsProvider->getUpdated($collection, (int)$store->getId());
            foreach ($this->batch->getItems($updated, $this->batchSize) as $batchDocuments) {
                $items = $this->mapper->get('update')->map($batchDocuments, (int)$store->getId());
                if (count($items)) {
                    try {
                        $this->logger->debug('[UpdateInBulk]');
                        $this->logger->debug(\Zend_Json::encode($items));
                        $this->itemHelper->updateItemsInBulk($items, $store, $indice);
                    } catch (\Exception $e) {
                        $this->logger->error(
                            sprintf(
                                '[Doofinder] There was an error while updating items in bulk: %s',
                                $e->getMessage()
                            )
                        );
                    }
                }
            }
            $collection->walk('delete');
        }
    }
    /**
     * Function to delete the products that have been stored into the data base as "to be deleted"
     */
    private function deleteProducts($store, $indice)
    {
        $collection = $this->changedProductCollectionFactory->create()->filterDeleted((int)$store->getId());
        if ($collection->getSize()) {
            $deleted = $this->documentsProvider->getDeleted($collection);
            foreach ($this->batch->getItems($deleted, $this->batchSize) as $batchDeleted) {
                $items = $this->mapper->get('delete')->map($batchDeleted, (int)$store->getId());
                if (count($items)) {
                    try {
                        $this->logger->debug('[DeleteInBulk]');
                        $this->logger->debug(\Zend_Json::encode($items));
                        $this->itemHelper->deleteItemsInBulk($items, $store, $indice);
                    } catch (\Exception $e) {
                        $this->logger->error(
                            sprintf(
                                '[Doofinder] There was an error while deleting items in bulk: %s',
                                $e->getMessage()
                            )
                        );
                    }
                }
            }
            $collection->walk('delete');
        }
    }
}
