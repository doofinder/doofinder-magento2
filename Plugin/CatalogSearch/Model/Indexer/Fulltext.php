<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamName
// phpcs:disable EcgM2.Plugins.Plugin.PluginError, Squiz.Commenting.FunctionComment.TypeHintMissing

/**
 * This class is responsible for aborting indexing in case it should not occur
 * immediatelly after updating a product (so it is later done by cron updates).
 */
class Fulltext
{
    /**
     * A constructor.
     *
     * @param StoreConfig $storeConfig
     */
    public function __construct(StoreConfig $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    /**
     * Conditionally aborts indexing after product bulk edit.
     *
     * @param FulltextIndexer $indexer
     * @param \Closure $closure
     * @param array $arg
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteList(
        FulltextIndexer $indexer,
        \Closure $closure,
        array $arg
    ) {
        if ($this->canProceed()) {
            $closure($arg);
        }
    }

    /**
     * Conditionally aborts indexing after single product edit.
     *
     * @param FulltextIndexer $indexer
     * @param \Closure $closure
     * @param $arg
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteRow(
        FulltextIndexer $indexer,
        \Closure $closure,
        $arg
    ) {
        if ($this->canProceed()) {
            $closure($arg);
        }
    }

    /**
     * Checks, whether indexing should occur immediately after products edit.
     *
     * It shouldn't in case:
     * - Doofinder is not set as internal search engine, because then it should be allowed to run index anyway,
     * - or Cron updates are enabled in admin and it means indexes will be refreshed upon next Cron update call.
     *
     * @return boolean Whether to allow running indexing.
     */
    private function canProceed()
    {
        return !$this->storeConfig->isDelayedUpdatesEnabled();
    }
}
