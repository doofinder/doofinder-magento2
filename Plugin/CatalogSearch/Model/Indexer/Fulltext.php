<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Indexer;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

/**
 * This class is responsible for aborting indexing in case it should not occur
 * immediatelly after updating a product (so it is later done by cron updates).
 */
class Fulltext
{
    /**
     * A constructor.
     *
     * @param Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(StoreConfig $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    /**
     * Conditionally aborts indexing after product bulk edit.
     *
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext $indexer
     * @param \Closure $closure
     * @param array $args
     *
     * @return void
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
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext $indexer
     * @param \Closure $closure
     * @param $args
     *
     * @return void
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
     * @return bool Whether to allow running indexing.
     */
    private function canProceed()
    {
        return !$this->storeConfig->isCronUpdatesEnabled();
    }
}
