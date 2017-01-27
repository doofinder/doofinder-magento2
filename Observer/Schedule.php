<?php

namespace Doofinder\Feed\Observer;

/**
 * Class Schedule
 *
 * @package Doofinder\Feed\Observer
 */
class Schedule implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Doofinder\Feed\Helper\Schedule $schedule
    ) {
        $this->_request = $request;
        $this->_schedule = $schedule;
    }

    /**
     * Execute observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // Check if user wants to reset the schedule
        $reset = (bool) $this->_request->getParam('reset');

        $this->_schedule->regenerateSchedule($reset, $reset);
    }
}
