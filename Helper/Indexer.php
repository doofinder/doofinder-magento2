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
     * @var Search
     */
    private $search;

    /**
     * @var \Magento\Framework\Search\Request\DimensionFactory
     */
    private $dimensionFactory;

    /**
     * Old doofinder section configs before save
     *
     * @var array
     */
    private $oldConfigs = [];

    /**
     * Indexer constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreConfig $storeConfig
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param Search $search
     * @param \Magento\Framework\Search\Request\DimensionFactory $dimensionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreConfig $storeConfig,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        Search $search,
        \Magento\Framework\Search\Request\DimensionFactory $dimensionFactory
    ) {
        parent::__construct($context);
        $this->storeConfig = $storeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->search = $search;
        $this->dimensionFactory = $dimensionFactory;
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
        $ignore = ['categories_in_navigation'];
        foreach ($storeCodes as $storeCode) {
            $feedAttributes = $this->scopeConfig->getValue($storeConfig::FEED_ATTRIBUTES_CONFIG, $scope, $storeCode);
            $feedSettings = $this->scopeConfig->getValue($storeConfig::FEED_SETTINGS_CONFIG, $scope, $storeCode);
            $config = $feedAttributes + $feedSettings;

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
        if (!$this->storeConfig->isInternalSearchEnabled()) {
            // Doofinder is not set as Search Engine - no need to invalidate
            return false;
        }
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

    /**
     * Get store id from dimensions
     *
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return integer|null
     */
    public function getStoreIdFromDimensions(array $dimensions)
    {
        foreach ($dimensions as $dimension) {
            if ($dimension->getName() == 'scope') {
                return $dimension->getValue();
            }
        }

        return null;
    }

    /**
     * Get store code from dimensions
     *
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return integer|null
     */
    public function getStoreCodeFromDimensions(array $dimensions)
    {
        foreach ($dimensions as $dimension) {
            if ($dimension->getName() == 'scope') {
                $storeId = $dimension->getValue();
                return $this->storeConfig->getStoreCode($storeId);
            }
        }

        return null;
    }

    /**
     * Check, whether delayed product updates are enabled and active.
     *
     * For delayed products updates to work, Doofinder must be set as internal search engine.
     *
     * @return boolean True if Cron updates are enabled.
     */
    public function isDelayedUpdatesEnabled()
    {
        if (!$this->storeConfig->isInternalSearchEnabled()) {
            return false;
        }

        if ($this->isScheduled()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $dimensions
     * @return boolean
     */
    public function isAvailable(array $dimensions = [])
    {
        if (!$apiKey = $this->storeConfig->getApiKey()) {
            return false;
        }

        if (!$dimensions) {
            return true; // when Indexer mode is on save, dimensions are empty
        }

        $storeId = $this->getStoreIdFromDimensions($dimensions);
        $hashId = $this->storeConfig->getHashId($storeId);
        $searchEngines = $this->search->getDoofinderSearchEngines($apiKey);

        if (!isset($searchEngines[$hashId])) {
            return false;
        }
        return true;
    }

    /**
     * @param integer $storeId
     * @return \Magento\Framework\Search\Request\Dimension
     */
    public function getDimensions($storeId)
    {
        return $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
    }
}
