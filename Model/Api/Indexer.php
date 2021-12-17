<?php

namespace Doofinder\Feed\Model\Api;

use Doofinder\Management\ManagementClientFactory;
use Doofinder\Management\ManagementClient;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Helper\Serializer;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Doofinder\Feed\Helper\Logger;
use Doofinder\Feed\Helper\Utils;


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
     * doofinderLogger
     *
     * @var mixed
     */
    private $doofinderLogger;

    /**
     * @param ManagementClientFactory $managementClientFactory
     * @param StoreConfig $storeConfig
     * @param IndexerHelper $indexerHelper
     * @param Serializer $serializer
     * @param Logger $doofinderlogger
     */
    public function __construct(
        ManagementClientFactory $managementClientFactory,
        StoreConfig             $storeConfig,
        IndexerHelper           $indexerHelper,
        Serializer              $serializer,
        Logger                  $doofinderlogger

    )
    {
        $this->managementClientFactory = $managementClientFactory;
        $this->storeConfig = $storeConfig;
        $this->indexerHelper = $indexerHelper;
        $this->serializer = $serializer;
        $this->doofinderLogger = $doofinderlogger;

    }

    /**
     * Delete specific Doofinder Index
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */

    public function deleteDoofinderIndex(array $dimensions, $indexName)
    {
        $response = null;
        try {
            $hashId = $this->getHashId($dimensions);
            $response = $this->getClient()->deleteIndex(
                $hashId,
                $indexName
            );
            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'deleteDoofinderIndex', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId], 'response' => Utils::validateJSON($response)]));
        } catch (\Exception $exception) {
            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'deleteDoofinderIndex', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId], 'response' => Utils::validateJSON($response)], 'exception' => ['message' => $exception->getMessage(), 'stacktrace' => $exception->getTraceAsString()]));

        }

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
     * Create new Doofinder index
     * @param array $dimensions
     * @param string $indexName
     * @return void
     */
    public function createDoofinderIndex(array $dimensions, $indexName)
    {
        $response = null;
        try {
            $hashId = $this->getHashId($dimensions);
            $options = [
                'options' => [
                    'exclude_out_of_stock_items' => false,
                    'group_variants' => false,
                ],
                'name' => $indexName,
                'preset' => 'product'
            ];
            $response = $this->getClient()->createIndex(
                $hashId,
                $this->serializer->serialize($options)
            );


            if ($this->storeConfig->isIndexingLogsAllowed()) {
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'createDoofinderIndex', 'payload' => ['hashid' => $hashId, 'options' => $options], 'response' => Utils::validateJSON($response)]));
            } else {
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'createDoofinderIndex'], 'response' => Utils::validateJSON($response)));
            }

        } catch (\Exception $exception) {
            if ($this->storeConfig->isIndexingLogsAllowed()) {
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'createDoofinderIndex', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'options' => $options], 'response' => Utils::validateJSON($response)], 'exception' => ['message' => $exception->getMessage(), 'stacktrace' => $exception->getTraceAsString()]));
            } else {
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'createDoofinderIndex', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId], 'response' => Utils::validateJSON($response)], 'exception' => ['message' => $exception->getMessage(), 'stacktrace' => $exception->getTraceAsString()]));
            }
        }
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
        $response = null;
        try {

            $response = $this->getClient()->createTemporaryIndex(
                $hashId,
                $indexName
            );

            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'createDoofinderIndexTemp', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId], 'response' => Utils::validateJSON($response)]));

        } catch (\Exception $exception) {
            $this->getClient()->deleteTemporaryIndex($hashId, $indexName);

            $response = $this->getClient()->createTemporaryIndex(
                $hashId,
                $indexName
            );
            //logging caught error
            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'createDoofinderIndexTemp', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId], 'response' => Utils::validateJSON($response), 'exception' => ['message' => $exception->getMessage(), 'stacktrace' => $exception->getTraceAsString()]]));
        }
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

            return (bool)$index->getName();
        } catch (\Exception $exception) {
            //logging caught error
            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'isIndexExists', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId]], 'exception' => ['message' => $exception->getMessage(), 'stacktrace' => $exception->getTraceAsString()]));

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

        $response = null;
        $hashId = $this->getHashId($dimensions);
        try {
            $response = $this->getClient()->createTempBulk(
                $hashId,
                $indexName,
                $this->serializer->serialize($items)
            );

            if ($this->storeConfig->isIndexingLogsAllowed()) {
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'addItems', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'items' => $items], 'response' => Utils::validateJSON($response)]));
            } else {
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'addItems', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'itemscount' => count($items)], 'response' => Utils::validateJSON($response)]));
            }
        } catch (\Exception $ex) {
            if ($this->storeConfig->isIndexingLogsAllowed()) {
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'addItems', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'items' => $items], 'reponse' => Utils::validateJSON($response)], 'exception' => ['message' => $ex->getMessage(), 'stacktrace' => $ex->getTraceAsString()]));
            } else {
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'addItems', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'itemscount' => count($items)], 'reponse' => Utils::validateJSON($response)], 'exception' => ['message' => $ex->getMessage(), 'stacktrace' => $ex->getTraceAsString()]));
            }
        }
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
        $count = 2;
        $response = null;
        do {
            try {
                $response = $this->getClient()->createBulk(
                    $hashId,
                    $indexName,
                    $this->serializer->serialize($items)
                );
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'updateItems', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'items' => $items], 'response' => Utils::validateJSON($response)]));
                break;
            } catch (\Exception $ex) {
                $count = $count - 1;
                //log here
                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'updateItems', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'items' => $items]], 'exception' => ['message' => $ex->getMessage(), 'stacktrace' => $ex->getTraceAsString()]));
            }

        } while ($count > 0);
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
        $response = null;
        try {
            $response = $this->getClient()->deleteBulk(
                $hashId,
                $indexName,
                $this->serializer->serialize($items)
            );

            //log
            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'deleteItems', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'items' => $items], 'response' => Utils::validateJSON($response)]));

        } catch (\Exception $ex) {
            //error log
            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['name' => 'Indexer', 'Desc' => 'communicates between magento to doofinder '], 'Location' => ['function' => 'deleteItems', 'payload' => ['indexname' => $indexName, 'hashid' => $hashId, 'items' => $items]], 'exception' => ['message' => $ex->getMessage(), 'stacktrace' => $ex->getTraceAsString()]));
        }
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
}

