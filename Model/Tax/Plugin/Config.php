<?php

namespace Doofinder\Feed\Model\Tax\Plugin;

/**
 * Tax config plugin
 */
class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Force price conversion
     *
     * We need to force price conversion only in case
     * of prices stored and displayed without tax, while
     * price export mode is set to 'with tax'.
     * All other tax setting combinations are fine.
     *
     * @param  \Magento\Tax\Model\Config $taxConfig
     * @param  \Closure $closure
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
     */
    public function aroundNeedPriceConversion(\Magento\Tax\Model\Config $taxConfig, \Closure $closure)
    {
        // phpcs:enable
        $needPriceConversion = $closure();

        // Already needs price conversion so do nothig
        if ($needPriceConversion !== false) {
            return $needPriceConversion;
        }

        $taxMode = $this->scopeConfig->getValue(
            \Doofinder\Feed\Helper\StoreConfig::FEED_SETTINGS_CONFIG . '/price_tax_mode'
        );

        // Force price conversion only in case of 'with tax' price export mode
        return $taxMode == \Doofinder\Feed\Model\Config\Source\Feed\PriceTaxMode::MODE_WITH_TAX;
    }
}
