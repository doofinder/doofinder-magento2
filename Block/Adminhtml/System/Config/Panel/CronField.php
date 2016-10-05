<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

class CronField extends Message
{
    /**
     * @const ERROR_PREFIX Error prefix
     */
    const ERROR_PREFIX = "#error#";

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_datetime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_timezone;

    /**
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Magento\Framework\Stdlib\DateTime $datetime
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Framework\Stdlib\DateTime $datetime,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_schedule = $schedule;
        $this->_datetime = $datetime;
        $this->_timezone = $timezone;
        parent::__construct($context, $data);
    }

    /**
     * Get element text
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function getText(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $storeId = $this->_request->getParam('store');
        $store = $this->_storeManager->getStore($storeId);

        if ($field = $this->getField($element->getName())) {
            $text = $this->getProcessFieldValue($store->getCode(), $field);

            $class = 'feed-message';

            // Mark an error
            if (strpos($text, self::ERROR_PREFIX) !== false) {
                $text = str_replace(self::ERROR_PREFIX, '', $text);
                $class .= ' error';
            }

            return $text ? ('<span class="' . $class . '">' . $text . '</span>') : '';
        }

        return '';
    }

    /**
     * Get process field value
     *
     * @param string $storeCode
     * @param string $field
     * @return string
     */
    protected function getProcessFieldValue($storeCode, $field)
    {
        $process = $this->_schedule->getProcessByStoreCode($storeCode);

        if (!$process || !$process->getId()) {
            switch ($field) {
                case 'status':
                    return __('Not created');

                case 'message':
                    return __('Process not created yet, it will be created automatically by cron job');
            }

            return '';
        }

        $value = $process->getData($field);

        switch ($field) {
            case 'next_run':
            case 'next_iteration':
                $date = new \DateTime($value);
                $date->setTimezone(new \DateTimeZone($this->_timezone->getConfigTimezone()));

                $value = $this->_datetime->formatDate($date);
                break;

            case 'last_feed_name':
                if ($this->_schedule->isFeedFileExist($storeCode)) {
                    $url = $this->_schedule->getFeedFileUrl($storeCode);
                    $value = '<a href="' . $url . ' target="_blank">' . __('Get %1', $value) . '</a>';
                } else {
                    $value = __('Currently there is no file to preview.');
                }

                break;
        }

        return $value;
    }

    /**
     * Get field from element name
     *
     * @param string $name
     * @return string
     */
    protected function getField($name = null)
    {
        $pattern = '/groups\[[^[]+\]\[fields\]\[([a-z_-]*)\]\[value\]/';
        $preg = preg_match($pattern, $name, $match);
        if ($preg && isset($match[1])) {
            return $match[1];
        } else {
            return false;
        }
    }
}
