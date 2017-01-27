<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

class Cron extends Message
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
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->_timezone = $timezone;
        parent::__construct($context, $data);
    }

    /**
     * Get element text
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function getText(\Magento\Framework\Data\Form\Element\AbstractElement $element)
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
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="label"></td>';
        $html .= $this->_renderValue($element);
        return $this->_decorateRowHtml($element, $html);
    }
}
