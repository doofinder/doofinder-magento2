<?php

namespace Doofinder\Feed\Cron;

use Doofinder\Feed\Model\ChangedProduct\Processor;

/**
 * Class PerfromDelayedUpdates
 * This class reflects current product data in Doofinder on cron run.
 */
class PerformDelayedUpdates
{

    /**
     * @var Processor
     */
    private $processor;

    /**
     * PerformDelayedUpdates constructor.
     * @param Processor $processor
     */
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Processes all product change traces for each store view.
     *
     * @return void
     */
    public function execute()
    {
        $this->processor->execute();
    }
}
