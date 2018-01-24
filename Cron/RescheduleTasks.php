<?php

namespace Doofinder\Feed\Cron;

/**
 * Reschedule cron action
 */
class RescheduleTasks
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * GenerateFeed constructor.
     *
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     */
    public function __construct(
        \Doofinder\Feed\Helper\Schedule $schedule
    ) {
        $this->schedule = $schedule;
    }

    /**
     * Execute this cron job.
     *
     * @return $this
     */
    public function execute()
    {
        $this->schedule->regenerateSchedule();

        return $this;
    }
}
