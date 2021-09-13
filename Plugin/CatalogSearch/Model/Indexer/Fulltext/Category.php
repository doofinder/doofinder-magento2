<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer\Fulltext;

use ArrayIterator;
use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Doofinder\Feed\Registry\IndexerScope;
use Magento\Framework\Model\AbstractModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Model\ChangedProduct\Registration;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Indexer\IndexerHandlerFactory;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\Framework\Indexer\ConfigInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Catalog search indexer plugin for catalog category.
 */
class Category extends AbstractPlugin
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
     * indexerScope
     *
     * @var mixed
     */
    private $indexerScope;
    
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
     * fulltextResource
     *
     * @var mixed
     */
    private $fulltextResource;
    
    /**
     * logger
     *
     * @var mixed
     */
    private $logger;




    /**
     * @param Registration $registration=
     * @param StoreConfig $storeConfig
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Registration $registration,
        IndexerScope $indexerScope,
        StoreConfig $storeConfig,
        IndexerRegistry $indexerRegistry,
        IndexerHelper $indexerHelper,
        ConfigInterface $config,
        IndexerHandlerFactory $indexerHandlerFactory,
        FullFactory $fullActionFactory,
        FulltextResource $fulltextResource,
        PsrLoggerInterface $logger

    ) {
        $this->registration = $registration;
        $this->storeConfig = $storeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->indexerScope = $indexerScope;
        $this->indexerHelper = $indexerHelper;
        $this->config = $config;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->fullActionFactory = $fullActionFactory;
        $this->fulltextResource = $fulltextResource;
        $this->logger = $logger;
    }
    
    /**
     * getIdsOnly
     *
     * @param  mixed $allproducts
     */
    public function getIdsOnly($allproducts)
    {
        //set empty array
        $entityIds = [];
        try 
        {
            foreach ($allproducts as $product) {
                //just get the id only
                $entityIds[] = (int)$product->getId();
            }
            //return created product ids as array
            return $entityIds;
        } catch (\Exception $e) {
            return [];
        }
    }
     
    /**
     * aroundSave
     *
     * @param  mixed $resourceCategory
     * @param  mixed $proceed
     * @param  mixed $category
     * @return void
     */
    public function aroundSave(ResourceCategory $resourceCategory, callable $proceed, AbstractModel $category)
    {
        //get the old category name
        $origname = $category->getOrigData('name');
        //get the category new name
        $newname = $category->getData('name');
        $result = $proceed($category);

        try {
            //check if there is a change in name then we get all affectec products
            if ($origname != $newname) {

                if (is_array($category->getAffectedProductIds())) {
                    $this->updateDoofinder(array_values($category->getAffectedProductIds()));
                } else {

                    //fetch all products assigned to this category
                    $allproducts = $this->getIdsOnly($category->getProductCollection());
                    //if the name has changed then we check for the affected items
                    $this->updateDoofinder($allproducts);
                }
            } else {
                //if the name has not changed then we just get the affected items
                $affectedProducts = $category->getAffectedProductIds();
                if (is_array($affectedProducts)) {
                    $this->updateDoofinder(array_values($affectedProducts));
                }
            }
        } catch (\Exception $ex) 
        {
            $this->logger->error($ex->getMessage());    
            return $result;
        }
        return $result;
    }
    
    /**
     * updateDoofinder
     *
     * @param  mixed $productarray
     * @return void
     */
    private function updateDoofinder($productarray)
    {
        //we know the products are affected  if this is not null
        $stores = $this->storeConfig->getAllStores();
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);

        $data = $this->config->getIndexers()['catalogsearch_fulltext'];

        $indexerHandler = $this->createDoofinderIndexerHandler($data);

        $fullAction = $this->createFullAction($data);
        //
        foreach ($stores as $store) {
            if ($this->storeConfig->isUpdateByApiEnable($store->getCode())) {
                if ($indexer->isScheduled()) 
                {
                    foreach ($productarray as $id) {
                        try
                        {
                            //delete 
                            $this->registration->registerDelete(
                                $id,
                                $store->getCode()
                                );

                            //recreate using update
                            $this->registration->registerUpdate(
                                $id,
                                $store->getCode()
                            );
                         }
                        catch (\Exception $ex) 
                        {
                            $this->logger->error($ex->getMessage());
                        }
                    }
                } 
                else 
                {
                    try 
                    {
                        //get dimensions
                        $dimensions = array($this->indexerHelper->getDimensions($store->getId()));
                       //get store id and cast to integer
                        $storeId = (int)$this->indexerHelper->getStoreIdFromDimensions($dimensions);

                        $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                       //
                        $productIds = array_unique(
                            array_merge($productarray, $this->fulltextResource->getRelationsByChild($productarray))
                        );
                        
                        $indexerHandler->deleteIndex(
                            $dimensions,
                            new \ArrayIterator($productIds)
                        );

                        $indexerHandler->saveIndex(
                            $dimensions,
                            $fullAction->rebuildStoreIndex($storeId,$productIds)
                        );

                    } catch (\Exception $ex) {
                        //log the caught error
                        $this->logger->error($ex->getMessage());
                    }
                     finally {
                        $this->indexerScope->setIndexerScope(null);
                        return;
                    }
                }
            }
        }
    }

    private function createDoofinderIndexerHandler(array $data = [])
    {
        return $this->indexerHandlerFactory->create($data);
    }

    private function createFullAction(array $data)
    {
        return $this->fullActionFactory->create(['data' => $data]);
    }


    public function aroundDelete(ResourceCategory $resourceCategory, callable $proceed, AbstractModel $category)
    {
        //get the products that will be affected
        $allproducts = $this->getIdsOnly($category->getProductCollection());

        $result = $proceed($category);
        $stores = $this->storeConfig->getAllStores();
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
        $data = $this->config->getIndexers()['catalogsearch_fulltext'];
  
        $indexerHandler = $this->createDoofinderIndexerHandler($data);
   
        $fullAction = $this->createFullAction($data);

        

        foreach ($stores as $store) {
            if ($this->storeConfig->isUpdateByApiEnable($store->getCode())) {

                if ($indexer->isScheduled()) {
                    foreach ($allproducts  as $productid) {
                        try
                        {
                        $this->registration->registerDelete(
                            $productid,
                            $store->getCode()
                        );

                        $this->registration->registerUpdate(
                            $productid,
                            $store->getCode()
                        );
                    }
                    catch (\Exception $ex) 
                    {
                        $this->logger->error($ex->getMessage());
                    }
                    }
                }
                else
                {
                    try 
                    {
                        $dimensions = array($this->indexerHelper->getDimensions($store->getId()));
                        //get store id and cast to integer
                        $storeId = (int)$this->indexerHelper->getStoreIdFromDimensions($dimensions);
                        //
                        $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                        //
                        $productIds = array_unique(array_merge($allproducts, $this->fulltextResource->getRelationsByChild($allproducts)));

                        $indexerHandler->saveIndex(
                            $dimensions,
                            $fullAction->rebuildStoreIndex($storeId,$productIds)
                        );
                     
                    }
                    catch (\Exception $ex) 
                    {
                        $this->logger->error($ex->getMessage());
                    } 
                    finally 
                    {
                        $this->indexerScope->setIndexerScope(null);
                        return;
                    }
                }
            }
        }
        return $result;
    }
}
