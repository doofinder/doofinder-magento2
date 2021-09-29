<?php

namespace Doofinder\Feed\Model\Api;

use Doofinder\Management\ManagementClientFactory;
use Doofinder\Management\ManagementClient;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Helper\Serializer;
use Psr\Log\LoggerInterface as PsrLoggerInterface;


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
     * logger
     *
     * @var mixed
     */
    private $logger;
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
        Serializer $serializer,
        PsrLoggerInterface $logger
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->storeConfig = $storeConfig;
        $this->indexerHelper = $indexerHelper;
        $this->serializer = $serializer;
        $this->logger = $logger;

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
        try 
        {
           $response =  $this->getClient()->createTemporaryIndex(
                $hashId,
                $indexName
            );
            
            $this->logger->info('CreateIndex'.$hashId.' Index '.$indexName.' Reponse '.$response);    

        } catch (\Exception $exception) {
            $this->getClient()->deleteTemporaryIndex($hashId, $indexName);
            $this->getClient()->createTemporaryIndex(
                $hashId,
                $indexName
            );
            //logging caught error
            $this->logger->error('Doofinder : CreateIndex '.$hashId.' Index '.$indexName.' Error '.$exception->getMessage());    

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
        try 
        {
            $index = $this->getClient()->getIndex(
                $hashId,
                $indexName
            );
            return (bool) $index->getName();
        } catch (\Exception $exception) {
            //logging caught error
            $this->logger->error('Doofinder : '.$exception->getMessage());    
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
       
            try
            {
                $hashId = $this->getHashId($dimensions);
                $response = $this->getClient()->createTempBulk(
                    $hashId,
                    $indexName,
                    $this->serializer->serialize($items)
                );
                $this->logger->info('Doofinder : AddItems '.$hashId.' Index :  '.$indexName.' Response '.$response);    

             }
            catch(\Exception $ex)
            {   
                $this->logger->error('Doofinder : AddItems '.$hashId.' Index  : '.$indexName.' '.$ex->getMessage());    
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
     
        $count = 2;           
        do
        {          
            try
            {                                            
              $hashId = $this->getHashId($dimensions);                
              $response = $this->getClient()->createBulk(
                    $hashId,
                    $indexName,
                    $this->serializer->serialize($items)
                );                
                $this->logger->info('Doofinder : UpateItems '.$hashId.' Index :  '.$indexName.' Response '.$response);    

            break;
                
            }
            catch(\Exception $ex)
            {   
               $count =$count - 1;
               //log here
               $this->logger->error('Doofinder : UpateItems '.$hashId.' Index  : '.$indexName.' '.$ex->getMessage());    


            }               
        
           }while($count > 0);
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
            try
            {                      
               $response =  $this->getClient()->deleteBulk(
                    $hashId,
                    $indexName,
                    $this->serializer->serialize($items)
                );
            $this->logger->info('Doofinder : DeleteItems '.$hashId.' Index :  '.$indexName.' Response '.$response);    

            }
            catch(\Exception $ex)
            {
                $this->logger->error('Doofinder : DeleteItems '.$hashId.' Index  : '.$indexName.' '.$ex->getMessage());    

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

