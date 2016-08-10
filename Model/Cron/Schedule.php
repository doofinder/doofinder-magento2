<?php

namespace Doofinder\Feed\Model\Cron;

/**
 * Class Schedule
 *
 * @package Doofinder\Feed\Model\Cron
 */
class Schedule
{
    const JOB_CODE = 'feed_generate';

    /**
     * @var \Magento\Cron\Model\ScheduleFactory
     */
    protected $_scheduleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezone;

    /**
     * Schedule constructor.
     *
     * @param \Magento\Cron\Model\ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Magento\Cron\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_scheduleFactory = $scheduleFactory;
        $this->_timezone = $timezone;
    }

    /**
     * Create cron schedule now.
     */
    public function generateScheduleNow()
    {
        try {
            $this->_scheduleFactory->create()
                ->setJobCode(self::JOB_CODE)
                ->setStatus(\Magento\Cron\Model\Schedule::STATUS_PENDING)
                ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $this->_timezone->scopeTimeStamp()))
                ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $this->_timezone->scopeTimeStamp()+60))
                ->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Schedule could not be created'));
        }
    }
}