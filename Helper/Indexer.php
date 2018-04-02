<?php

namespace Doofinder\Feed\Helper;

/**
 * Indexer helper
 */
class Indexer extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @const CONFIG_SECTION_ID
     */
    const CONFIG_SECTION_ID = 'doofinder_config_config';

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * Old doofinder section configs before save
     *
     * @var array
     */
    private $oldConfigs = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        parent::__construct($context);
        $this->storeConfig = $storeConfig;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Get configs (for all stores in scope)
     *
     * @return array
     */
    private function getConfigs()
    {
        $scope = $this->storeConfig->getScopeStore();
        $storeCodes = $this->storeConfig->getStoreCodes();
        $storeConfig = $this->storeConfig;

        $configs = [];
        $ignore = ['password', 'categories_in_navigation'];
        foreach ($storeCodes as $storeCode) {
            $config = array_merge(
                $this->scopeConfig->getValue($storeConfig::FEED_ATTRIBUTES_CONFIG, $scope, $storeCode),
                $this->scopeConfig->getValue($storeConfig::FEED_SETTINGS_CONFIG, $scope, $storeCode)
            );

            $config = array_diff_key($config, array_flip($ignore));
            $configs[$storeCode] = $config;
        }

        return $configs;
    }

    /**
     * Store old config doofinder section config
     *
     * This comes from a config plugin that
     * loads 'doofinder_config_config' section config
     * right before config update.
     *
     * @return void
     */
    public function storeOldConfig()
    {
        $this->oldConfigs = $this->getConfigs();
    }

    /**
     * Check if index should be invalidated
     *
     * @return boolean
     */
    public function shouldIndexInvalidate()
    {
        if (empty($this->oldConfigs)) {
            // No old config - assume index should invalidate
            return true;
        }

        $configs = $this->getConfigs();

        if (array_keys($configs) != array_keys($this->oldConfigs)) {
            // Configs keys should match - assume index should invalidate
            return true;
        }

        foreach (array_keys($configs) as $storeCode) {
            if (!$this->storeConfig->isInternalSearchEnabled($storeCode)) {
                // Doofinder internal search disabled, proceed
                continue;
            }

            if ($configs[$storeCode] != $this->oldConfigs[$storeCode]) {
                // Configs does not match - invalidate
                return true;
            }
        }

        // Configs for each store matches - index does not need invalidating
        return false;
    }

    /**
     * Invalidate index
     *
     * @return void
     */
    public function invalidate()
    {
        $indexer = $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID);
        $indexer->invalidate();
    }

    /**
     * Checks if index is run by schedule
     *
     * @return boolean
     */
    public function isScheduled()
    {
        $indexer = $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID);
        return $indexer->isScheduled();
    }
}
