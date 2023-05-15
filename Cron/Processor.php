<?php

declare(strict_types=1);

namespace Doofinder\Feed\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Doofinder\Feed\Helper\Indice as IndiceHelper;
use Doofinder\Feed\Helper\Item as ItemHelper;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedProduct\DocumentsProvider;
use Doofinder\Feed\Model\Indexer\Data\Mapper;
use Doofinder\Feed\Model\ProductRepository;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\CollectionFactory as ChangedProductCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
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

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(
        Context $context,
        StoreConfig $storeConfig,
        ChangedProductCollectionFactory $changedProductCollectionFactory,
        DocumentsProvider $documentsProvider,
        ItemHelper $itemHelper,
        Mapper $mapper,
        Batch $batch,
        LoggerInterface $logger,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ProductRepository $productRepository,
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
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context);
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
     * Batches have been removed because it is very very unlikely to have a lot of products related with the update on save process
     */
    private function createProducts($store, $indice)
    {
        $storeId = (int)$store->getId();
        $collection = $this->changedProductCollectionFactory->create()->filterCreated($storeId);
        if ($collection->getSize()) {
            $searchCriteria = $this->createSearchCriteria($collection, $storeId);
            $items = $this->productRepository->getList($searchCriteria)->__toArray();
            if (count($items)) {
                try {
                    $this->logger->debug('[UpdateInBulk]');
                    $this->logger->debug(json_encode($items));
                    $this->itemHelper->updateItemsInBulk(
                        $items,
                        $store,
                        $indice
                    );
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf(
                            '[Doofinder] There was an error while updating items in bulk: %s',
                            $e->getMessage()
                        )
                    );
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
        $storeId = (int)$store->getId();
        $collection = $this->changedProductCollectionFactory->create()->filterUpdated($storeId);
        if ($collection->getSize()) {
            $searchCriteria = $this->createSearchCriteria($collection, $storeId);
            $items = $this->productRepository->getList($searchCriteria)->__toArray();
            if (count($items)) {
                try {
                    $this->logger->debug('[UpdateInBulk]');
                    $this->logger->debug(json_encode($items));
                    $this->itemHelper->updateItemsInBulk(
                        $items,
                        $store,
                        $indice
                    );
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf(
                            '[Doofinder] There was an error while updating items in bulk: %s',
                            $e->getMessage()
                        )
                    );
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

    private function createSearchCriteria($collection, $storeId)
    {
        $productIds = [];
        foreach ($collection as $item) {
            $productIds[] = $item['product_id'];
        }
            
        return $this->searchCriteriaBuilderFactory
            ->create()
            ->addFilter('entity_id', implode(',', $productIds), 'in')
            ->addFilter('store_id', $storeId)
            ->create();
    }
}
