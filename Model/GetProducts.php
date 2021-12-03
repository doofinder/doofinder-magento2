<?php

namespace Doofinder\Feed\Model;
use Magento\Framework\App\ResourceConnection;
use Doofinder\Feed\Helper\Logger;
use Doofinder\Feed\Helper\StoreConfig;

class GetProducts
{
    
    /**
     * productCollectionFactory
     *
     * @var undefined
     */
    private $productCollectionFactory = null;
    
    /**
     * doofinderLogger
     *
     * @var mixed
     */
    private $doofinderLogger;
  
    
    /**
     * storeConfig
     *
     * @var mixed
     */
    private $storeConfig;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory ,
        Logger $doofinderlogger,
        StoreConfig $storeConfig  
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->doofinderLogger = $doofinderlogger;
        $this->storeConfig = $storeConfig;
    }
    
    /**
     * getProductCollection
     *
     * @param  mixed $storeId
     * @return void
     */
    public function getProductCollection($storeId)
    {
       
        try 
        {
            $collection = $this->productCollectionFactory->create();
            $connection =  $collection->getConnection();
            $connection->beginTransaction();
            
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('entity_id');
            $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $collection->addAttributeToFilter('visibility',\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
            $collection->addStoreFilter($storeId);        
            $collection->setPageSize(100000);

            $connection->commit();
            //get only ids
            $ids = [];
            foreach ($collection as $product) 
            {
                $ids[] = $product->getId();   
            }
            return $ids;
        } catch (\Exception $e) 
        {
            $connection->rollBack();
            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(),array('File'=>__FILE__,'Type'=>['Class'=>'GetProducts'],'Location'=>['function'=>'getProductCollection'],'exception'=>['message'=>$e->getMessage(),'stacktrace'=>$e->getTraceAsString()]));

            throw $e;
        }
        
       
    }
}
