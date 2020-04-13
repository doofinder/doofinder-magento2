<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Registry\IndexerScope;
use Doofinder\Feed\Helper\StoreConfig;

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
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var IndexerScope
     */
    private $indexerScope;

    /**
     * A constructor.
     *
     * @param StoreConfig $storeConfig
     * @param IndexerScope $indexerScope
     */
    public function __construct(
        StoreConfig $storeConfig,
        IndexerScope $indexerScope
    ) {
        $this->storeConfig = $storeConfig;
        $this->indexerScope = $indexerScope;
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
        if ($this->storeConfig->isInternalSearchEnabled()) {
            $this->indexerScope->setIndexerScope(IndexerScope::SCOPE_FULL);
        }
    }
}
