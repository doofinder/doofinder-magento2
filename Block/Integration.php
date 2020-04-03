<?php

namespace Doofinder\Feed\Block;

/**
 * Class Integration
 * The class responsible for integrating Doofinder Layer
 */
class Integration extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

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
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Produce the integration script
     *
     * @return string
     */
    public function toHtml()
    {
        $script = $this->storeConfig->getSearchLayerScript();

        /**
         * Disable search autocomplete
         * NOTICE This works but could be done better
         */
        $script .= '<script type="text/javascript">';
        $script .=
            '(function(el){ el && el.removeAttribute(\'data-mage-init\'); })(document.getElementById(\'search\'));';
        $script .= '</script>';

        return $script;
    }
}
