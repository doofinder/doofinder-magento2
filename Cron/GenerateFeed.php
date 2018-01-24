<?php

namespace Doofinder\Feed\Cron;

/**
 * Generate feed cron action
 */
class GenerateFeed
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * GenerateFeed constructor.
     *
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     */
    public function __construct(
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig
    ) {
        $this->schedule = $schedule;
        $this->generatorFactory = $generatorFactory;
        $this->feedConfig = $feedConfig;
    }

    /**
     * Execute this cron job.
     *
     * @return \Doofinder\Feed\Cron\GenerateFeed
     */
    public function execute()
    {
        if ($process = $this->schedule->getActiveProcess()) {
            $this->schedule->runProcess($process);
        }

        return $this;
    }
}
