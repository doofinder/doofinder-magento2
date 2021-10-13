<?php
namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Model\AbstractModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Framework\Indexer\IndexerRegistry;
use Doofinder\Feed\Registry\IndexerScope;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Model\ChangedProduct\Registration;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Indexer\IndexerHandlerFactory;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Model\Indexer\IndexStructure;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Doofinder\Feed\Helper\Logger;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;

use \Doofinder\Search\Client as SearchClient;


use Exception;

/**
 * Catalog search indexer plugin for catalog product used to register product
 * updates when catalogsearch index update mode is set to "on schedule".
 */
class Product extends AbstractPlugin
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
     * doofinderLogger
     *
     * @var mixed
     */
    private $doofinderLogger;


    /**
     * @param Registration $registration
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
        FulltextResource $fulltextResource,
        Logger $doofinderlogger

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
        $this->doofinderLogger = $doofinderlogger;

    }

    

    /**
     * Reindex on product save.
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    

    public function afterSave(ResourceProduct $productResource, $result, AbstractModel $product)
    {
       // $product->setData('sed_extra_days',"Delivery estimated by 12 of November");
          if ($this->storeConfig->isDoofinderFeedConfigured())
          {
            $stores = $this->storeConfig->getAllStores();
            $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID); 
            foreach($stores as $store) 
            {
                //check if its update by API set
                if ($this->storeConfig->isUpdateByApiEnable($store->getCode()))
                {       
                   
                    //check the isScheduled variable if true
                    if($indexer->isScheduled()) 
                    {
                          //if true use registerdelete to delete the existing index
                          $this->registration->registerDelete(
                            $product->getId(), 
                            $store->getCode()
                            );

                            //if true use registerUpdate
                            $this->registration->registerUpdate(
                                $product->getId(), 
                                $store->getCode()
                            );

                            $log = array('File'=>__FILE__,'Type'=>['Plugin'=>'Product','Mode'=>'onSchedule'],'Location'=>['function'=>'afterSave','product'=>['productid'=>$product->getId(),'storecode'=> $store->getCode()]]);
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(),$log);

                    }
                    else
                    {
                        $data = $this->config->getIndexers()['catalogsearch_fulltext'];          
                        $fullAction = $this->createFullAction($data);
                        try
                        {
                            $indexerHandler = $this->createDoofinderIndexerHandler($data);
                        }
                        catch (\LogicException $e) 
                        {
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(),array('File'=>__FILE__,'Type'=>['Plugin'=>'Product','Mode'=>'onSave'],'Location'=>['function'=>'afterSave'],'exception'=>['message'=>$e->getMessage(),'stacktrace'=>$e->getTraceAsString()]));
                        }
                        //if it is false  its on save
                        //get dimensions
                        $dimensions = array($this->indexerHelper->getDimensions($store->getId()));
                        //get storeid
                        $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
                        
                        try 
                        {
                            $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                            //convert the id to array

                            $ids = array();
                            if(!is_array($product->getId()))
                            {
                                $ids = (array)$product->getId();
                            }
                            $entityIds = iterator_to_array( new \ArrayIterator($ids));

                            $productIds = array_unique(
                                array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds))
                            );

                            $indexerHandler->deleteIndex(
                                $dimensions,
                                new \ArrayIterator($productIds)
                            );
        
                            $indexerHandler->saveIndex(
                                $dimensions,
                                $fullAction->rebuildStoreIndex($storeId, $productIds)
                            );

                            $log = array('File'=>__FILE__,'Type'=>['Plugin'=>'Product','Mode'=>'onSave'],'Location'=>['function'=>'afterSave','product'=>['productid'=>$product->getId(),'storecode'=> $store->getCode()]]);
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(),$log);
        
                        } 
                        catch(\Exception $e) 
                        {
                            $log = array('File'=>__FILE__,'Type'=>['Plugin'=>'Product','Mode'=>'onSave'],'Location'=>['function'=>'afterSave','product'=>['productid'=>$product->getId(),'storecode'=> $store->getCode()],'exception'=>['message'=>$e->getMessage(),'stacktrace'=>$e->getTraceAsString()]]);
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(),$log);                          

                        }
                        finally 
                        {
                             $this->indexerScope->setIndexerScope(null);
                            return $result;
                        }
                    }
                   
                }

            }      
          }            
        return $result;
    }

    /**
     * Reindex on product delete
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function afterDelete(ResourceProduct $productResource, $result, AbstractModel $product)
    {

        if ($this->storeConfig->isDoofinderFeedConfigured())
        {
        $stores = $this->storeConfig->getAllStores();
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
       

        foreach($stores as $store) {
            //validate 
            if ($this->storeConfig->isUpdateByApiEnable($store->getCode()))
            {
                //check if its scheduled
                if($indexer->isScheduled()) 
                {
                    $this->registration->registerDelete(
                        $product->getId(), 
                        $store->getCode()
                    );

                    $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(),array('File'=>__FILE__,'Type'=>['Plugin'=>'Product','Mode'=>'onSchedule'],'Location'=>['function'=>'afterDelete','product'=>$product->getId()]));
                }
                else
                {
                    $data = $this->config->getIndexers()['catalogsearch_fulltext'];        
                    $fullAction = $this->createFullAction($data);
                    try
                    {
                       $indexerHandler = $this->createDoofinderIndexerHandler($data);
                    }catch (\LogicException $e) 
                    {
                        $this->logger->error($e->getMessage()); 
                    }
                    //its on save mode
                    $dimensions = array($this->indexerHelper->getDimensions($store->getId()));
                    $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
                    
                    try {
                        $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                        $entityIds = iterator_to_array($product->getId());
                        $productIds = array_unique(
                            array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds))
                        );
                        //delete
                        $indexerHandler->deleteIndex(
                            $dimensions,
                            new \ArrayIterator($productIds)
                        );

                        $indexerHandler->saveIndex(
                            $dimensions,
                            $fullAction->rebuildStoreIndex($storeId, $productIds)
                        );
                        $log = array('File'=>__FILE__,'Type'=>['Plugin'=>'Product','Mode'=>'onSave'],'Location'=>['function'=>'afterDelete','product'=>$product->getId()]);
                        $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(),$log);
    
                    } catch(\Exception $e) 
                    {
                        //log any cought error here                    
                        $log = array('File'=>__FILE__,'Type'=>['Plugin'=>'Product','Mode'=>'onSave'],'Location'=>['function'=>'afterDelete','exception'=>['message'=>$e->getMessage()]]);
                        $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(),$log);

                    } 
                    finally 
                    {
                        $this->indexerScope->setIndexerScope(null);
                        return $result;
                    }
                }
            }
        }
        }
        return $result;
    }
 
    private function createDoofinderIndexerHandler(array $data = []) {
        return $this->indexerHandlerFactory->create($data);
    }
  
    private function createFullAction(array $data) {
        return $this->fullActionFactory->create(['data' => $data]);
    }

}