<?php

namespace Doofinder\Feed\Model\Api;

use Doofinder\Management\ManagementClientFactory;
use Doofinder\Management\ManagementClient;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Helper\Serializer;

/**
 * Class Indexer
 * The class responsible for communicating between Magento and Doofinder API Library
 */
class Indexer
{
    /**
     * @var ManagementClientFactory
     */
    private $managementClientFactory;

    /**
     * @var ManagementClient
     */
    private $managementClient;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var IndexerHelper
     */
    private $indexerHelper;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Indexer constructor.
     * @param ManagementClientFactory $managementClientFactory
     * @param StoreConfig $storeConfig
     * @param IndexerHelper $indexerHelper
     * @param Serializer $serializer
     */
    public function __construct(
        ManagementClientFactory $managementClientFactory,
        StoreConfig $storeConfig,
        IndexerHelper $indexerHelper,
        Serializer $serializer
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->storeConfig = $storeConfig;
        $this->indexerHelper = $indexerHelper;
        $this->serializer = $serializer;
    }

    /**
     * Delete specific Doofinder Index
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */
    public function deleteDoofinderIndex(array $dimensions, $indexName)
    {
        $hashId = $this->getHashId($dimensions);
        $this->getClient()->deleteIndex(
            $hashId,
            $indexName
        );
    }

    /**
     * Create new Doofinder index
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */
    public function createDoofinderIndex(array $dimensions, $indexName)
    {
        $hashId = $this->getHashId($dimensions);
        $options = [
            'options' => [
                'exclude_out_of_stock_items' => false,
                'group_variants' => false,
            ],
            'name' => $indexName,
            'preset' => 'product'
        ];
        $this->getClient()->createIndex(
            $hashId,
            $this->serializer->serialize($options)
        );
    }

    /**
     * Create new temporary Doofinder index
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */
    public function createDoofinderIndexTemp(array $dimensions, $indexName)
    {
        $hashId = $this->getHashId($dimensions);
        $this->getClient()->createTemporaryIndex(
            $hashId,
            $indexName
        );
    }

    /**
     * Check if specified index is created in Doofinder API
     * @param string $indexName
     * @param array $dimensions
     * @return boolean
     */
    public function isIndexExists($indexName, array $dimensions)
    {
        $hashId = $this->getHashId($dimensions);
        try {
            $index = $this->getClient()->getIndex(
                $hashId,
                $indexName
            );
            return (bool) $index->getName();
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Add products to specified Doofinder index
     * @param array $items
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */
    public function addItems(array $items, array $dimensions, $indexName)
    {
        $hashId = $this->getHashId($dimensions);
        $this->getClient()->createTempBulk(
            $hashId,
            $indexName,
            $this->serializer->serialize($items)
        );
    }

    /**
     * Update products in specified Doofinder index
     * @param array $items
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */
    public function updateItems(array $items, array $dimensions, $indexName)
    {
        $hashId = $this->getHashId($dimensions);
        $this->getClient()->createBulk(
            $hashId,
            $indexName,
            $this->serializer->serialize($items)
        );
    }

    /**
     * @param array $items
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */
    public function deleteItems(array $items, array $dimensions, $indexName)
    {
        $hashId = $this->getHashId($dimensions);
        $this->getClient()->deleteBulk(
            $hashId,
            $indexName,
            $this->serializer->serialize($items)
        );
    }

    /**
     * Switch temporary index to the main one
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */
    public function switchIndex(array $dimensions, $indexName)
    {
        $hashId = $this->getHashId($dimensions);
        $this->getClient()->replace(
            $hashId,
            $indexName
        );
    }

    /**
     * Init Management client from Doofinder API Library
     * @return ManagementClient
     */
    public function getClient()
    {
        if (!$this->managementClient) {
            $this->managementClient = $this->managementClientFactory->create([
                'host' => $this->storeConfig->getManagementServer(),
                'token' => $this->storeConfig->getApiKey()
            ]);
        }
        return $this->managementClient;
    }

    /**
     * @param array $dimensions
     * @return string
     */
    private function getHashId(array $dimensions)
    {
        $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
        return $this->storeConfig->getHashId($storeId);
    }
}
