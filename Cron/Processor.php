<?php

declare(strict_types=1);

namespace Doofinder\Feed\Cron;

use Doofinder\Feed\Helper\Item as ItemHelper;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedItem\DocumentsProvider;
use Doofinder\Feed\Model\ChangedItem\ItemType;
use Doofinder\Feed\Model\ResourceModel\ChangedItem\CollectionFactory as ChangedItemCollectionFactory;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Psr\Log\LoggerInterface;

class Processor
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var ChangedItemCollectionFactory
     */
    private $changedItemCollectionFactory;

    /**
     * @var DocumentsProvider
     */
    private $documentsProvider;

    /**
     * @var ItemHelper
     */
    private $itemHelper;

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
        ChangedItemCollectionFactory $changedItemCollectionFactory,
        DocumentsProvider $documentsProvider,
        ItemHelper $itemHelper,
        Batch $batch,
        LoggerInterface $logger,
        $batchSize = 100
    ) {
        $this->storeConfig = $storeConfig;
        $this->changedItemCollectionFactory = $changedItemCollectionFactory;
        $this->documentsProvider = $documentsProvider;
        $this->itemHelper = $itemHelper;
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
                    foreach (ItemType::getList() as $itemType => $itemIndice) {
                        $this->manageItems($store, $itemType, $itemIndice);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('[Doofinder] Error processing updates: ' . $e->getMessage());
            }
        }
    }

    /**
     * Function to manage the products that have been stored into the data base
     */
    private function manageItems($store, $itemType, $indice)
    {
        $this->createItems($store, $itemType, $indice);
        $this->updateItems($store, $itemType, $indice);
        $this->deleteItems($store, $itemType, $indice);
    }

    private function createItems($store, $itemType, $indice)
    {
        $collection = $this->changedItemCollectionFactory->create()->filterCreated((int)$store->getId(), $itemType);
        if ($collection->getSize()) {
            $created = $this->documentsProvider->getBatched($collection, (int)$store->getId());
            foreach ($this->batch->getItems($created, $this->batchSize) as $batchDocuments) {
                $items = $this->mapProducts($batchDocuments);
                if (count($items)) {
                    try {
                        $this->logger->debug('[CreateInBulk]');
                        $this->logger->debug(json_encode($items));
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

    private function updateItems($store, $itemType, $indice)
    {
        $collection = $this->changedItemCollectionFactory->create()->filterUpdated((int)$store->getId(), $itemType);
        if ($collection->getSize()) {
            $updated = $this->documentsProvider->getBatched($collection, (int)$store->getId());
            foreach ($this->batch->getItems($updated, $this->batchSize) as $batchDocuments) {
                $items = $this->mapProducts($batchDocuments);
                if (count($items)) {
                    try {
                        $this->logger->debug('[UpdateInBulk]');
                        $this->logger->debug(json_encode($items));
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

    private function deleteItems($store, $itemType, $indice)
    {
        $collection = $this->changedItemCollectionFactory->create()->filterDeleted((int)$store->getId(), $itemType);
        if ($collection->getSize()) {
            $deleted = $this->documentsProvider->getBatched($collection);
            foreach ($this->batch->getItems($deleted, $this->batchSize) as $batchDeleted) {
                $items = $this->mapProducts($batchDeleted);
                if (count($items)) {
                    try {
                        $this->logger->debug('[DeleteInBulk]');
                        $this->logger->debug(json_encode($items));
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

    private function mapProducts($documents) {
        return array_map(function ($productId) {
            return ['id' => $productId];
        }, $documents);
    }
}
