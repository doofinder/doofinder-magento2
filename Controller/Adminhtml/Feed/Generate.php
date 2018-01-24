<?php

namespace Doofinder\Feed\Controller\Adminhtml\Feed;

/**
 * Cron 'generate now' controller
 */
class Generate extends \Magento\Backend\App\Action
{
    const FEED_GENERATION_MESSAGE = 'Feed generation has been scheduled';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * Generate constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Doofinder\Feed\Helper\Schedule $schedule
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->schedule = $schedule;

        parent::__construct($context);
    }

    /**
     * Create feed generation schedule now and return json message.
     *
     * @return mixed
     */
    public function execute()
    {
        $this->schedule->regenerateSchedule(true, true, true);

        return $this->resultJsonFactory->create()->setData(
            ['message' => self::FEED_GENERATION_MESSAGE]
        );
    }
}
