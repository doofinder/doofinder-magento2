<?php

namespace Doofinder\Feed\Model\Tax\Plugin;

/**
 * @class Config
 */
class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Force price conversion
     *
     * We need to force price conversion only in case
     * of prices stored and displayed without tax, while
     * price export mode is set to 'with tax'.
     * All other tax setting combinations are fine.
     *
     * @return boolean
     */
    public function aroundNeedPriceConversion(\Magento\Tax\Model\Config $taxConfig, \Closure $closure)
    {
        $needPriceConversion = $closure();

        // Already needs price conversion so do nothig
        if ($needPriceConversion !== false) {
            return $needPriceConversion;
        }

        $taxMode = $this->_scopeConfig->getValue(
            \Doofinder\Feed\Helper\StoreConfig::FEED_SETTINGS_CONFIG . '/price_tax_mode'
        );

        // Force price conversion only in case of 'with tax' price export mode
        return $taxMode == \Doofinder\Feed\Model\Config\Source\Feed\PriceTaxMode::MODE_WITH_TAX;
    }
}
