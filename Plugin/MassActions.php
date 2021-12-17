<?php

namespace Doofinder\Feed\Plugin;

use ArrayIterator;
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
use Doofinder\Feed\Helper\Logger;

class MassActions extends AbstractPlugin
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var Registration
     */
    private $registration;
    /**
     * @var StoreConfig
     */
    private $storeConfig;
    /**
     * @var IndexStructure
     */
    private $indexStructure;
    /**
     * @var IndexerHelper
     */
    private $indexerHelper;
    /**
     * @var IndexerScope
     */
    private $indexerScope;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var FullFactory
     */
    private $fullActionFactory;

    /**
     * @var PsrLoggerInterface
     */
    private $logger;

    /**
     * @var FulltextResource
     */
    private $fulltextResource;
    /**
     * @var Logger
     */
    private $doofinderLogger;

    /**
     * @param Registration $registration
     * @param StoreConfig $storeConfig
     * @param IndexerRegistry $indexerRegistry
     * @param IndexerHelper $indexerHelper
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param IndexStructure $indexStructure
     * @param ConfigInterface $config
     * @param IndexerScope $indexerScope
     * @param FullFactory $fullActionFactory
     * @param PsrLoggerInterface $logger
     * @param FulltextResource $fulltextResource
     * @param Logger $doofinderlogger
     */
    public function __construct(Registration $registration, StoreConfig $storeConfig, IndexerRegistry $indexerRegistry, IndexerHelper $indexerHelper, IndexerHandlerFactory $indexerHandlerFactory, IndexStructure $indexStructure, ConfigInterface $config, IndexerScope $indexerScope, FullFactory $fullActionFactory, PsrLoggerInterface $logger, FulltextResource $fulltextResource, Logger $doofinderlogger

    )
    {
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
     * @param ProductAction $subject
     * @param ProductAction $action
     * @param $productIds
     * @param $attrData
     * @return ProductAction
     */
    public function afterUpdateAttributes(ProductAction $subject, ProductAction $action, $productIds, $attrData)
    {
        try {
            if ($this->storeConfig->isDoofinderFeedConfigured()) {
                //get all stores
                $stores = $this->storeConfig->getAllStores();
                $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
                //loop through each store
                foreach ($stores as $store) {
                    //check if its update by API set
                    if ($this->storeConfig->isUpdateByApiEnable($store->getCode())) {
                        //check the isScheduled variable if true
                        if ($indexer->isScheduled()) {
                            //loop through all product ids
                            foreach ($productIds as $id) {
                                try {
                                    //if true use registerUpdate
                                    $this->registration->registerUpdate($id, $store->getCode());
                                } catch (Exception $e) {
                                    $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'MassActions', 'Mode' => 'onSchedule'], 'Location' => ['function' => 'afterUpdateAttributes', 'product' => ['productid' => $id, 'storecode' => $store->getCode()], 'stacktrace' => $e->getMessage()]));
                                }
                            }
                            $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'MassActions', 'Mode' => 'onSchedule'], 'Location' => ['function' => 'afterUpdateAttributes', 'product' => ['productids' => $productIds, 'storecode' => $store->getCode(), 'attributedata' => $attrData]]));
                        } else {
                            //if it is false  its on save
                            try {
                                $data = $this->config->getIndexers()['catalogsearch_fulltext'];

                                //create index handler
                                $indexerHandler = $this->indexerHandlerFactory->create($data);

                                $fullAction = $this->fullActionFactory->create(['data' => $data]);
                                //get dimensions
                                $dimensions = array($this->indexerHelper->getDimensions($store->getId()));
                                //get storeid
                                $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);

                                $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                                //convert the ids to array
                                $entityIds = iterator_to_array(new ArrayIterator($productIds));
                                $newproductIds = array_unique(array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds)));
                                //save index
                                $indexerHandler->saveIndex($dimensions, $fullAction->rebuildStoreIndex($storeId, $newproductIds));
                                //write logs
                                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'MassActions', 'Mode' => 'onSave'], 'Location' => ['function' => 'afterUpdateAttributes', 'product' => ['productid' => $newproductIds, 'storecode' => $store->getCode()]]));

                            } catch (Exception $e) {
                                $this->doofinderLogger->writeLogs($this->storeConfig->getLogSeverity(), array('File' => __FILE__, 'Type' => ['Plugin' => 'MassActions', 'Mode' => 'onSave'], 'Location' => ['function' => 'afterUpdateAttributes', 'product' => ['productid' => $id, 'storecode' => $store->getCode()], 'exception' => ['message' => $e->getMessage()]]));
                            } finally {
                                $this->indexerScope->setIndexerScope(null);
                            }
                        }
                    }
                }
            }
        } catch (Exception $er) {
            $this->logger->error($er->getMessage());
        }
        return $action;
    }

}
