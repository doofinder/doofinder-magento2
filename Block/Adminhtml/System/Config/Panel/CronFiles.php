<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

class CronFiles extends Message
{
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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_schedule = $schedule;
        $this->_storeConfig = $storeConfig;
        $this->_storeManager = $storeManager;
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
        $storeCodes = $this->_storeConfig->getStoreCodes();

        $files = [];

        foreach ($storeCodes as $storeCode) {
            if ($this->_schedule->isFeedFileExist($storeCode)) {
                $url = $this->_schedule->getFeedFileUrl($storeCode);
                $files[] = '<a href="' . $url . ' target="_blank">' . $url . '</a>';
            } else {
                $files[] = __('Currently there is no file to preview.');
            }
        }

        $html = '';

        if (count($files) > 1) {
            $html .= '<ul>';
            foreach ($files as $storeCode => $file) {
                $store = $this->_storeManager->getStore($storeCode);
                $html .= '<li><b>' . $store->getName() . ':</b><div>' . $file . '</div></li>';
            }
            $html .= '</ul>';
        } else {
            $html .= reset($files);
        }

        return $html;
    }
}
