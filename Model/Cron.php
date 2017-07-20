<?php

namespace Doofinder\Feed\Model;

/**
 * Class Cron
 *
 * @package Doofinder\Feed\Model
 */
class Cron extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_MISSED = 'missed';
    const STATUS_ERROR = 'error';
    const STATUS_DISABLED = 'disabled';
    const STATUS_WAITING = 'waiting';

    const MSG_EMPTY = "Currently there is no message.";
    const MSG_PENDING = "The new process has been registered and it's waiting to be activated.";
    const MSG_DISABLED = "The feed generator for this view is currently disabled.";
    const MSG_WAITING = "Waiting for registering the new process of generating the feed.";
    const MSG_SUCCESS = "Last process successfully completed. Now waiting for new schedule.";

    /**
     * Object initialization.
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
    // @codingStandardsIgnoreEnd
        $this->_init('\Doofinder\Feed\Model\ResourceModel\Cron');
    }

    /**
     * Check if cronjob is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->getStatus() != self::STATUS_DISABLED) {
            return true;
        }

        return false;
    }

    /**
     * Check if cronjob is waiting.
     *
     * @return bool
     */
    public function isWaiting()
    {
        if ($this->getStatus() == self::STATUS_WAITING) {
            return true;
        }

        return false;
    }

    /**
     * Enable process (set status to waiting).
     *
     * @return \Doofinder\Feed\Model\Cron
     */
    public function enable()
    {
        if ($this->getStatus() == self::STATUS_DISABLED) {
            $this->setStatus(self::STATUS_WAITING)->save();
        }

        return $this;
    }

    /**
     * Disable process (set status to disabled).
     *
     * @return \Doofinder\Feed\Model\Cron
     */
    public function disable()
    {
        $this->setStatus(self::STATUS_DISABLED)->save();

        return $this;
    }
}
