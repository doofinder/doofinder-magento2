<?php

namespace Doofinder\Feed\Block;

/**
 * Class Integration
 * @package Doofinder\Feed\Block
 */
class Integration extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        $this->_storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Produce the integration script
     *
     * @return string
     */
    public function toHtml()
    {
        $script = $this->_storeConfig->getSearchLayerScript();

        /**
         * Disable search autocomplete
         * NOTICE This works but could be done better
         */
        $script .= '<script type="text/javascript">';
        $script .= 'document.getElementById(\'search\').removeAttribute(\'data-mage-init\');';
        $script .= '</script>';

        return $script;
    }
}
