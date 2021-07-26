<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Registry\IndexerScope;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Magento\Framework\Indexer\ConfigInterface;
use Doofinder\Feed\Model\ChangedProduct\Registration;
use Doofinder\Feed\Model\Indexer\Processor;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Doofinder\Feed\Model\ChangedProductFactory;
use Doofinder\Feed\Model\Indexer\IndexerHandlerFactory;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\Framework\App\ObjectManager;
use Doofinder\Feed\Model\Indexer\IndexStructure;

// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamName
// phpcs:disable EcgM2.Plugins.Plugin.PluginError, Squiz.Commenting.FunctionComment.TypeHintMissing

/**
 * Class Fulltext
 * The class responsible for setting indexer scope
 * that will be used in Doofinder Indexer Handler
 */
class Fulltext
{
    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var ChangedProductFactory
     */
    private $changedFactory;

    /**
     * @var IndexerHelper
     */
    private $indexerHelper;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Registration
     */
    protected $registration;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var FulltextResource
     */
    private $fulltextResource;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

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
     * A constructor.
     *
     * @param FullFactory $fullActionFactory
     * @param StoreConfig $storeConfig
     * @param IndexerScope $indexerScope
     * @param ConfigInterface $config
     * @param Registration $registration
     * @param Processor $processor
     * @param FulltextResource $fulltextResource
     * @param ChangedProductFactory $changedFactory
     * @param IndexerHelper $indexerHelper
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param IndexStructure $indexStructure
     */
    public function __construct(
        FullFactory $fullActionFactory,
        StoreConfig $storeConfig,
        IndexerScope $indexerScope,
        ConfigInterface $config,
        Registration $registration,
        Processor $processor,
        FulltextResource $fulltextResource,
        ChangedProductFactory $changedFactory,
        IndexerHelper $indexerHelper,
        IndexerHandlerFactory $indexerHandlerFactory,
        IndexStructure $indexStructure
    ) {
        $this->fullActionFactory = $fullActionFactory;
        $this->storeConfig = $storeConfig;
        $this->indexerScope = $indexerScope;
        $this->config = $config;
        $this->registration = $registration;
        $this->processor = $processor;
        $this->fulltextResource = $fulltextResource;
        $this->changedFactory = $changedFactory;
        $this->indexerHelper = $indexerHelper;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->indexStructure = $indexStructure;
    }

    /**
     * @param FulltextIndexer $indexer
     * @param mixed ...$args
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
     */
    public function beforeExecuteFull(FulltextIndexer $indexer, ...$args)
    {
        // phpcs:enable
        $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_FULL);
    }

    public function beforeExecuteByDimensions(FulltextIndexer $indexer, array $dimensions, \Traversable $entityIds = null)
    {
        if ($this->indexerScope->getIndexerScope() != null) {
            return;
        }
        $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
        if ($this->storeConfig->isUpdateByApiEnable($storeId)) {
            if ($this->indexerHelper->isScheduled()) {
                $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_DELAYED);
            } else {
                $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
            }
        }
    }

    /**
     * Execute after plugin (update/delete on doofinder indice) ONLY when theese conditions are met:
     *  - doofinder is not the search engine
     *  - update doofinder indice mode is set by API
     *  - the catalogsearch index update mode is set to on save 
     */
    public function afterExecuteByDimensions(FulltextIndexer $indexer, $result, array $dimensions, \Traversable $entityIds = null)
    {
        $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
        if (
            $this->indexerScope->getIndexerScope() == $this->indexerScope::SCOPE_ON_SAVE ||
            $this->indexerScope->getIndexerScope() == $this->indexerScope::SCOPE_FULL
        ) {
            $data = $this->config->getIndexers()['catalogsearch_fulltext'];
            $indexerHandler = $this->createDoofinderIndexerHandler($data);
            $storeId = $this->indexerHelper->getStoreIdFromDimensions($dimensions);
            $fullAction = $this->createFullAction($data);
            if (null === $entityIds) {
                try {
                    // reindexall
                    $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_FULL);
                    // create temp index
                    $this->indexStructure->create(null, [], $dimensions);
                    // add items temp index, switch temporary index to the main one
                    $indexerHandler->saveIndex(
                        $dimensions,
                        $fullAction->rebuildStoreIndex($storeId)
                    );
                } catch(\Exception $e) {
                    throw $e;
                } finally {
                    $this->indexerScope->setIndexerScope(null);
                }
            } else {
                try {
                    $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_ON_SAVE);
                    $entityIds = iterator_to_array($entityIds);
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

                } catch(\Exception $e) {
                    throw $e;
                } finally {
                    $this->indexerScope->setIndexerScope(null);
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
