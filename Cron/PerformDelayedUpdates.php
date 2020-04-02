<?php

namespace Doofinder\Feed\Cron;

use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Model\ChangedProduct\Processor;

/**
 * Class PerfromDelayedUpdates
 * This class reflects current product data in Doofinder on cron run.
 */
class PerformDelayedUpdates
{
    /**
     * @var IndexerHelper
     */
    private $helper;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * PerformDelayedUpdates constructor.
     * @param IndexerHelper $helper
     * @param Processor $processor
     */
    public function __construct(IndexerHelper $helper, Processor $processor)
    {
        $this->helper = $helper;
        $this->processor = $processor;
    }

    /**
     * Processes all product change traces for each store view.
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->helper->isDelayedUpdatesEnabled()) {
            return;
        }

        $this->processor->execute();
    }
}
