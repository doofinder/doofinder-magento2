<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

/**
 * Cron field
 */
class CronField extends Message
{
    /**
     * @const ERROR_PREFIX Error prefix
     */
    const ERROR_PREFIX = "#error#";

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->schedule = $schedule;
        $this->timezone = $context->getLocaleDate();
        parent::__construct($context, $data);
    }

    /**
     * Get element text
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function getText(\Magento\Framework\Data\Form\Element\AbstractElement $element)
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
     * Get last feed name
     *
     * @param string $storeCode
     * @param string $feedName
     * @return string
     */
    private function getLastFeedName($storeCode, $feedName)
    {
        if ($this->schedule->isFeedFileExist($storeCode)) {
            $url = $this->schedule->getFeedFileUrl($storeCode);
            return '<a href="' . $url . ' target="_blank">' . __('Get %1', $feedName) . '</a>';
        }

        return __('Currently there is no file to preview.');
    }

    /**
     * Get next date
     *
     * @param string $value
     * @return string
     */
    private function getNextDate($value)
    {
        /**
         * NOTICE Using '-' in database is defenitely wrong practice
         */
        if ($value == '-') {
            return $value;
        }

        $date = $this->timezone->scopeDate(null, $value);
        return $this->timezone->formatDateTime($date);
    }

    /**
     * Get process field value
     *
     * @param string $storeCode
     * @param string $field
     * @return string
     */
    private function getProcessFieldValue($storeCode, $field)
    {
        $process = $this->schedule->getProcessByStoreCode($storeCode);

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
                $value = $this->getNextDate($value);
                break;

            case 'last_feed_name':
                $value = $this->getLastFeedName($storeCode, $value);
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
    private function getField($name = null)
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
