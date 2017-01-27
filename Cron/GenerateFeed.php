<?php

namespace Doofinder\Feed\Cron;

/**
 * Class GenerateFeed
 *
 * @package Doofinder\Feed\Cron
 */
class GenerateFeed
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    protected $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    protected $_feedConfig;

    /**
     * GenerateFeed constructor.
     *
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     */
    public function __construct(
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig
    ) {
        $this->_schedule = $schedule;
        $this->_generatorFactory = $generatorFactory;
        $this->_feedConfig = $feedConfig;
    }

    /**
     * Execute this cron job.
     *
     * @return \Doofinder\Feed\Cron\GenerateFeed
     */
    public function execute()
    {
        if ($process = $this->_schedule->getActiveProcess()) {
            $this->_schedule->runProcess($process);
        }

        return $this;
    }
}
