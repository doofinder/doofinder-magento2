<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer\Fulltext;

use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Doofinder\Feed\Registry\IndexerScope;
use Magento\Framework\Model\AbstractModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;
use Magento\Store\Model\StoreDimensionProvider;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Model\ChangedProduct\Registration;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Indexer\IndexerHandlerFactory;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\Framework\Indexer\ConfigInterface;





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
     * @var StoreDimensionProvider
     */
    private $storeDimensionProvider;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    private $indexerScope;

    private $indexerHelper;

    protected $config;

    private $indexerHandlerFactory;

    private $fullActionFactory;

    private $fulltextResource;






    /**
     * @param Registration $registration
     * @param StoreDimensionProvider $storeDimensionProvider
     * @param StoreConfig $storeConfig
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Registration $registration,
        StoreDimensionProvider $storeDimensionProvider,
        IndexerScope $indexerScope,
        StoreConfig $storeConfig,
        IndexerRegistry $indexerRegistry,
        IndexerHelper $indexerHelper,
        ConfigInterface $config,
        IndexerHandlerFactory $indexerHandlerFactory,
        FullFactory $fullActionFactory,
        FulltextResource $fulltextResource
    ) {
        $this->registration = $registration;
        $this->storeDimensionProvider = $storeDimensionProvider;
        $this->storeConfig = $storeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->indexerScope = $indexerScope;
        $this->indexerHelper = $indexerHelper;
        $this->config = $config;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->fullActionFactory = $fullActionFactory;
        $this->fulltextResource = $fulltextResource;


    }

   public function getIdsOnly($allproducts)
   {
    $entityIds = [];
        try 
        {
            foreach ($allproducts as $product) 
            {
                $entityIds[] = $product->getId();
            }
        return $entityIds;
        } catch(\Exception $e) {
            return [];
        }

   }

    public function aroundSave(ResourceCategory $resourceCategory, callable $proceed, AbstractModel $category)
    {
        //get the old category name
        $origname = $category->getOrigData('name');

        //get the category new name
        $newname = $category->getData('name');
        $result = $proceed($category);

        //check if there is a change in name then we get all affectec products
        if ($origname != $newname) {
            if (is_array($category->getAffectedProductIds())) {
                    $this->updateDoofinder($category->getAffectedProductIds());
            } else {

                //fetch all products assigned to this category
                $allproducts = $this->getIdsOnly($category->getProductCollection());
                //if the name has changed then we check for the affected items
                $this->updateDoofinder($allproducts);
 
            }

        } else {
            //if the name has not changed then we just get the affected items
            $affectedProducts = $category->getAffectedProductIds();
            $entityIds = [];
            foreach ( $affectedProducts as $product) 
            {
                $entityIds[] = $product;
            }
            if (is_array($affectedProducts)) {
                    $this->updateDoofinder($entityIds);
            }
        }

        return $result;
    }


    public function updateDoofinder($productarray)
    {
        //we know the products are affected  if this is not null
        $stores = $this->storeConfig->getAllStores();
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);

        $data = $this->config->getIndexers()['catalogsearch_fulltext'];
        $indexerHandler = $this->createDoofinderIndexerHandler($data);
        $fullAction = $this->createFullAction($data);

        foreach ($stores as $store) {
            if ($this->storeConfig->isUpdateByApiEnable($store->getCode())) {
                if($indexer->isScheduled()) 
                {
                    foreach($productarray as $id)
                    {
                        $this->registration->registerUpdate(
                            $id,
                            $store->getCode()
                        );
                 }
                }
                else
                {
                    try 
                    {
                        $dimensions = array($this->indexerHelper->getDimensions($store->getId()));
                        $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                        $productIds = array_unique(
                            array_merge($productarray, $this->fulltextResource->getRelationsByChild($productarray))
                        );

                        $indexerHandler->saveIndex(
                            $dimensions,
                            $fullAction->rebuildStoreIndex($store->getCode(), $productIds)
                        );
                } catch(\Exception $e) {
                    throw $e;
                } finally {
                    $this->indexerScope->setIndexerScope(null);
                }

                }
            }
        }
    }

    private function createDoofinderIndexerHandler(array $data = []) {
        return $this->indexerHandlerFactory->create($data);
    }

    private function createFullAction(array $data) {
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

                if ($indexer->isScheduled()) 
                {
                    foreach ($allproducts  as $productid) {
                        $this->registration->registerDelete(
                            $productid,
                            $store->getCode()
                        );
                    }
                }
               
            }
        }
        return $result;
    }
}
