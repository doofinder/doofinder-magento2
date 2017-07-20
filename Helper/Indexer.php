<?php

namespace Doofinder\Feed\Helper;

class Indexer extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @const CONFIG_SECTION_ID
     */
    const CONFIG_SECTION_ID = 'doofinder_feed_feed';

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $_indexerRegistry;

    /**
     * Old doofinder section configs before save
     *
     * @var array
     */
    private $_oldConfigs = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        parent::__construct($context);
        $this->_storeConfig = $storeConfig;
        $this->_scopeConfig = $scopeConfig;
        $this->_indexerRegistry = $indexerRegistry;
    }

    /**
     * Get configs (for all stores in scope)
     *
     * @return array
     */
    private function getConfigs()
    {
        $scope = $this->_storeConfig->getScopeStore();
        $storeCodes = $this->_storeConfig->getStoreCodes();
        $storeConfig = $this->_storeConfig;

        $configs = [];
        $ignore = ['password', 'categories_in_navigation'];
        foreach ($storeCodes as $storeCode) {
            $config = array_merge(
                $this->_scopeConfig->getValue($storeConfig::FEED_ATTRIBUTES_CONFIG, $scope, $storeCode),
                $this->_scopeConfig->getValue($storeConfig::FEED_SETTINGS_CONFIG, $scope, $storeCode)
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
     * loads 'doofinder_feed_feed' section config
     * right before config update.
     */
    public function storeOldConfig()
    {
        $this->_oldConfigs = $this->getConfigs();
    }

    /**
     * Check if index should be invalidated
     *
     * @param string $scope
     * @param int $scopeId
     * @return boolean
     */
    public function shouldIndexInvalidate()
    {
        if (empty($this->_oldConfigs)) {
            // No old config - assume index should invalidate
            return true;
        }

        $configs = $this->getConfigs();

        if (array_keys($configs) != array_keys($this->_oldConfigs)) {
            // Configs keys should match - assume index should invalidate
            return true;
        }

        foreach (array_keys($configs) as $storeCode) {
            if (!$this->_storeConfig->isInternalSearchEnabled($storeCode)) {
                // Doofinder internal search disabled, proceed
                continue;
            }

            if ($configs[$storeCode] != $this->_oldConfigs[$storeCode]) {
                // Configs does not match - invalidate
                return true;
            }
        }

        // Configs for each store matches - index does not need invalidating
        return false;
    }

    /**
     * Invalidate index
     */
    public function invalidate()
    {
        $indexer = $this->_indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID);
        $indexer->invalidate();
    }
}
