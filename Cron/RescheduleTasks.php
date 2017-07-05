<?php

namespace Doofinder\Feed\Cron;

/**
 * Class RescheduleTasks
 *
 * @package Doofinder\Feed\Cron
 */
class RescheduleTasks
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * GenerateFeed constructor.
     *
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     */
    public function __construct(
        \Doofinder\Feed\Helper\Schedule $schedule
    ) {
        $this->_schedule = $schedule;
    }

    /**
     * Execute this cron job.
     *
     * @return $this
     */
    public function execute()
    {
        $this->_schedule->regenerateSchedule();

        return $this;
    }
}
