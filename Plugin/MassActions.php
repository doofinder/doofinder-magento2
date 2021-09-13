<?php

namespace Doofinder\Feed\Plugin;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Framework\Indexer\IndexerRegistry;
use Doofinder\Feed\Registry\IndexerScope;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Model\ChangedProduct\Registration;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Indexer\IndexerHandlerFactory;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Model\Indexer\IndexStructure;
use Exception;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;

class MassActions extends AbstractPlugin
{
      /**
     * @var Registration
     */
    private $registration;


    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;
    
    /**
     * indexStructure
     *
     * @var mixed
     */
    private $indexStructure;
    
    /**
     * indexerHelper
     *
     * @var mixed
     */
    private $indexerHelper;
    
    /**
     * config
     *
     * @var mixed
     */
    protected $config;
    
    /**
     * indexerScope
     *
     * @var mixed
     */
    private $indexerScope;
    
    /**
     * indexerHandlerFactory
     *
     * @var mixed
     */
    private $indexerHandlerFactory;
    
    /**
     * fullActionFactory
     *
     * @var mixed
     */
    private $fullActionFactory;
    
    /**
     * logger
     *
     * @var mixed
     */
    private $logger;

    
    /**
     * fulltextResource
     *
     * @var mixed
     */
    private $fulltextResource;

    /**
     * @param Registration $registration
     * @param StoreDimensionProvider $storeDimensionProvider
     * @param StoreConfig $storeConfig
     * @param IndexerRegistry $indexerRegistry
     */
    
    
    /**
     * __construct
     *
     * @return void
     */    
 
    public function __construct(
        Registration $registration,
        StoreConfig $storeConfig,
        IndexerRegistry $indexerRegistry,
        IndexerHelper $indexerHelper,
        IndexerHandlerFactory $indexerHandlerFactory,
        IndexStructure $indexStructure,
        ConfigInterface $config,      
        IndexerScope $indexerScope,
        FullFactory $fullActionFactory,
        PsrLoggerInterface $logger,
        FulltextResource $fulltextResource
    ) {
        $this->registration = $registration;
        $this->storeConfig = $storeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->indexerHelper = $indexerHelper;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->indexStructure = $indexStructure;
        $this->config = $config;
        $this->indexerScope = $indexerScope;
        $this->fullActionFactory = $fullActionFactory;
        $this->logger = $logger;
        $this->fulltextResource = $fulltextResource;
    }

   
    public function afterUpdateAttributes(ProductAction $subject,ProductAction $action, $productIds) 
    {
        try
        {
            //get the affected ids
            $stores = $this->storeConfig->getAllStores();
            $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID); 
            $data = $this->config->getIndexers()['catalogsearch_fulltext'];
            
            $indexerHandler = $this->createDoofinderIndexerHandler($data);
            $fullAction = $this->createFullAction($data);

            foreach($stores as $store) 
            {
                //check if its update by API set
                if ($this->storeConfig->isUpdateByApiEnable($store->getCode()))
                {             
                    //check the isScheduled variable if true
                    if($indexer->isScheduled()) 
                    {
                        //loop through all product ids
                        foreach($productIds as $id)
                        {                           
                            try
                            {
                                //delete first
                                $this->registration->registerDelete(
                                    $id, 
                                    $store->getCode()
                                );
                                //if true use registerUpdate
                                $this->registration->registerUpdate(
                                    $id, 
                                    $store->getCode()
                                );
                            }
                            catch(\Exception $e) 
                            {
                                $this->logger->error($e->getMessage());                     

                            }
                        
                        }
                    }
                    else
                    {
                        //if it is false  its on save
                          
                        try 
                        {
                             //get dimensions
                            $dimensions = array($this->indexerHelper->getDimensions($store->getId()));
                            //get storeid
                            $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
                     
                            $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                            //convert the id to array
                            $entityIds = iterator_to_array( new \ArrayIterator($productIds));

                            $newproductIds = array_unique(
                                array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds))
                            );
                            //delete index first
                            $indexerHandler->deleteIndex(
                                $dimensions,
                                new \ArrayIterator($newproductIds)
                            );

                            //save index
        
                            $indexerHandler->saveIndex(
                                $dimensions,
                                $fullAction->rebuildStoreIndex($storeId, $newproductIds)
                            );
        
                        } catch(\Exception $e) 
                        {
                            $this->logger->error($e->getMessage());                             

                        } finally {
                            $this->indexerScope->setIndexerScope(null);                      
                        }
                    }
                
                }

            }      
        }
        catch(Exception $er)
        {
            $this->logger->error("Error ".$er);  
        }     
        return $action;
    }
    private function createDoofinderIndexerHandler(array $data = []) {
        return $this->indexerHandlerFactory->create($data);
    }
  
    private function createFullAction(array $data) {
        return $this->fullActionFactory->create(['data' => $data]);
    }
}