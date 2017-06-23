<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

class CronFiles extends Message
{
    /**
     * @const ALLOWED_TIME 12 Hours in seconds
     */
    const ALLOWED_TIME = 43200;

    /**
     * @const NO_CRON_TASKS_MSG
     */
    const NO_CRON_TASKS_MSG = 'There are no registered cron tasks. ' .
                              'Please, check your system\'s crontab configuration.';

    /**
     * @const CRON_NOT_FINISHED_MSG
     */
    const CRON_NOT_FINISHED_MSG = 'There are no finished cron tasks. ' .
                                  'Please, check you system\'s crontab configuration.';

    /**
     * @const CRON_DELAYED_MSG
     */
    const CRON_DELAYED_MSG = 'Cron was run for the last time at %1. ' .
                             'Taking into account the settings of the step delay option, ' .
                             'there might be problems with the cron\'s configuration.';

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    protected $_scheduleCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_timezone;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->_timezone = $timezone;
        $this->_schedule = $schedule;
        $this->_storeConfig = $storeConfig;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get cron message
     *
     * @return string
     */
    protected function getCronMessage()
    {
        $collection = $this->_scheduleCollectionFactory->create();
        $collection->setOrder('finished_at', 'desc');
        $collection->setPageSize(1);

        if (!$collection->getSize()) {
            return __(self::NO_CRON_TASKS_MSG);
        }

        $schedule = $collection->getFirstItem();
        $finishedAt = $schedule->getData('finished_at');
        if (!$finishedAt) {
            return __(self::CRON_NOT_FINISHED_MSG);
        }

        /**
         * Get finished time in config timezone
         * Magento transform cron time's to config scope timezone
         */
        $finishedTime = new \DateTime($finishedAt, new \DateTimeZone($this->_timezone->getConfigTimezone()));

        // If difference in seconds is bigger than allowed, display message
        if ((time() - $finishedTime->getTimestamp()) > self::ALLOWED_TIME) {
            return __(
                self::CRON_DELAYED_MSG,
                $finishedAt
            );
        }

        return '';
    }

    /**
     * Get element text
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function getText(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $storeCodes = $this->_storeConfig->getStoreCodes();

        $enabled = false;
        $messages = array();

        foreach ($storeCodes as $storeCode) {
            $store = $this->_storeManager->getStore($storeCode);
            $config = $this->_storeConfig->getStoreConfig($storeCode);

            if (!$config['enabled']) {
                $message = __('Cron-based feed generation is <strong>disabled</strong>.');
            } elseif ($config['enabled']) {
                $enabled = true;

                if ($this->_schedule->isFeedFileExist($storeCode)) {
                    $url = $this->_schedule->getFeedFileUrl($storeCode);
                    $message = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
                } else {
                    $message = __('Currently there is no file to preview.');
                }

                $message .= '<p>';
                $message .= __(
                    'Cron-based feed generation is <strong>enabled</strong>. ' .
                    'Feed generation is being scheduled at %s:%s.',
                    $config['start_time'][0],
                    $config['start_time'][1]
                );
                $message .= '</p>';
            }

            $messages[$store->getName()] = $message;
        }

        $html = '';

        if ($enabled) {
            $html .= $this->getCronMessage();
        }

        if (count(array_unique($messages)) == 1) {
            return $html . reset($messages);
        }

        foreach ($messages as $storeName => $message) {
            $store = $this->_storeManager->getStore($storeCode);
            $html .= '<p><strong>' . $storeName . ':</strong></p><p>' . $message . '</p>';
        }

        return $html;
    }
}
