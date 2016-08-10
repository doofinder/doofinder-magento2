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
    protected $_resultJsonFactory;

    /**
     * @var \Doofinder\Feed\Model\Cron\ScheduleFactory
     */
    protected $_scheduleFactory;

    /**
     * Generate constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Doofinder\Feed\Model\Cron\ScheduleFactory $scheduleFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Doofinder\Feed\Model\Cron\ScheduleFactory $scheduleFactory
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_scheduleFactory = $scheduleFactory;

        parent::__construct($context);
    }

    /**
     * Create feed generation schedule now and return json message.
     *
     * @return mixed
     */
    public function execute()
    {
        $this->_scheduleFactory->create()->generateScheduleNow();

        return $this->_resultJsonFactory->create()->setData(
            ['message' => self::FEED_GENERATION_MESSAGE]
        );
    }
}