<?php

namespace Doofinder\Feed\Controller\Adminhtml\Feed;

/**
 * Class Generate
 *
 * @package Doofinder\Feed\Controller\Adminhtml\Feed
 */
class Generate extends \Magento\Backend\App\Action
{
    const FEED_GENERATION_MESSAGE = 'Feed generation has been scheduled';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $_resultJsonFactory;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * Generate constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Doofinder\Feed\Helper\Scheduler $scheduler
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Doofinder\Feed\Helper\Schedule $schedule
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_schedule = $schedule;

        parent::__construct($context);
    }

    /**
     * Create feed generation schedule now and return json message.
     *
     * @return mixed
     */
    public function execute()
    {
        $this->_schedule->regenerateSchedule(true, true, true);

        return $this->_resultJsonFactory->create()->setData(
            ['message' => self::FEED_GENERATION_MESSAGE]
        );
    }
}
