<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

/**
 * Cron files
 */
class CronFiles extends Message
{
    /**
     * @const ALLOWED_TIME 12 Hours in seconds
     */
    const ALLOWED_TIME = 43200;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    private $scheduleColFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $scheduleColFactory
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $scheduleColFactory,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->scheduleColFactory = $scheduleColFactory;
        $this->timezone = $context->getLocaleDate();
        $this->schedule = $schedule;
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get cron message
     *
     * @return string
     */
    private function getCronMessage()
    {
        $collection = $this->scheduleColFactory->create();
        $collection->setOrder('finished_at', 'desc');
        $collection->setPageSize(1);

        if (!$collection->getSize()) {
            return __(
                'There are no registered cron tasks. ' .
                'Please, check your system\'s crontab configuration.'
            );
        }

        $items = $collection->getItems();
        $schedule = reset($items);
        $finishedAt = $schedule->getData('finished_at');
        if (!$finishedAt) {
            return __(
                'There are no finished cron tasks. ' .
                'Please, check you system\'s crontab configuration.'
            );
        }

        /**
         * Get finished time in config timezone
         * Magento transform cron time's to config scope timezone
         */
        $finishedTime = $this->timezone->date($finishedAt);

        // If difference in seconds is bigger than allowed, display message
        if ((time() - $finishedTime->getTimestamp()) > self::ALLOWED_TIME) {
            return __(
                'Cron was run for the last time at %1. ' .
                'Taking into account the settings of the step delay option, ' .
                'there might be problems with the cron\'s configuration.',
                $finishedAt
            );
        }

        return '';
    }

    /**
     * Get element text
     *
     * @return string
     */
    public function getText()
    {
        $storeCodes = $this->storeConfig->getStoreCodes();

        $enabled = false;
        $messages = [];

        foreach ($storeCodes as $storeCode) {
            $store = $this->_storeManager->getStore($storeCode);
            $config = $this->storeConfig->getStoreConfig($storeCode);

            if (!$config['enabled']) {
                $message = __('Cron-based feed generation is <strong>disabled</strong>.');
            } elseif ($config['enabled']) {
                $enabled = true;

                if ($this->schedule->isFeedFileExist($storeCode)) {
                    $url = $this->schedule->getFeedFileUrl($storeCode);
                    $message = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
                } else {
                    $message = __('Currently there is no file to preview.');
                }

                $date = $this->timezone->date(null, null, false);
                $date->setTime(...$config['start_time']);
                $date = $this->timezone->date($date);

                $message .= '<p>';
                $message .= __(
                    'Cron-based feed generation is <strong>enabled</strong>. ' .
                    'Feed generation is being scheduled at %1.',
                    $date->format('H:i:s')
                );
                $message .= '</p>';
            }

            $messages[$store->getName()] = $message;
        }

        $html = '';

        if ($enabled) {
            $html .= $this->getCronMessage();
        }

        if (count(array_unique($messages)) > 1) {
            foreach ($messages as $storeName => $message) {
                $store = $this->_storeManager->getStore($storeCode);
                $html .= '<p><strong>' . $storeName . ':</strong></p><p>' . $message . '</p>';
            }

            return $html;
        }

        // Return single message
        return $html . reset($messages);
    }
}
