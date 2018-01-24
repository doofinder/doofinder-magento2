<?php

namespace Doofinder\Feed\Observer;

/**
 * Schedule observer
 */
class Schedule implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Doofinder\Feed\Helper\Schedule $schedule
    ) {
        $this->request = $request;
        $this->schedule = $schedule;
    }

    /**
     * Execute observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    // @codingStandardsIgnoreEnd
        // Check if user wants to reset the schedule
        $reset = (bool) $this->request->getParam('reset');

        $this->schedule->regenerateSchedule($reset, $reset);
    }
}
