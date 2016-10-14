<?php

namespace Doofinder\Feed\Helper;

/**
 * Class StoreConfig
 *
 * @package Doofinder\Feed\Helper
 */
class StoreConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Path to attributes config in config.xml/core_config_data
     */
    const FEED_ATTRIBUTES_CONFIG = 'doofinder_feed_feed/feed_attributes';

    /**
     * Path to cron config in config.xml/core_config_data
     */
    const FEED_CRON_CONFIG = 'doofinder_feed_feed/feed_cron';

    /**
     * Path to feed settings in config.xml/core_config_data
     */
    const FEED_SETTINGS_CONFIG = 'doofinder_feed_feed/feed_settings';

    /**
     * Path to internal search settings in config.xml/core_config_data
     */
    const INTERNAL_SEARCH_CONFIG = 'doofinder_feed_search/doofinder_internal_search';

    /**
     * Path to catalog search engine setting
     */
    const CATALOG_SEARCH_ENGINE_CONFIG = 'catalog/search/engine';

    /**
     * Doofinder search engine name
     */
    const DOOFINDER_SEARCH_ENGINE_NAME = 'doofinder';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * StoreConfig constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Return array with store config.
     *
     * @param string|null $storeCode
     * @return array
     */
    public function getStoreConfig($storeCode = null)
    {
        if (!$storeCode) {
            $storeCode = $this->getStoreCode();
        }

        $scopeStore = $this->getScopeStore();

        $config = array_merge(
            ['store_code' => $storeCode],
            ['attributes' => $this->_scopeConfig->getValue(self::FEED_ATTRIBUTES_CONFIG, $scopeStore, $storeCode)],
            $this->_scopeConfig->getValue(self::FEED_CRON_CONFIG, $scopeStore, $storeCode),
            $this->_scopeConfig->getValue(self::FEED_SETTINGS_CONFIG, $scopeStore, $storeCode)
        );

        $config['start_time'] = explode(',', $config['start_time']);

        return $config;
    }

    /**
     * Get store code.
     *
     * @return string Store code
     */
    public function getStoreCode($store = null)
    {
        return $this->_storeManager->getStore($store)->getCode();
    }

    /**
     * Get active/all store codes
     *
     * @param boolean $onlyActive = true
     * @return string[]
     */
    public function getStoreCodes($onlyActive = true)
    {
        $currentStoreCode = $this->getStoreCode();
        $storeCodes = [];

        if ($currentStoreCode == 'admin') {
            $stores = $this->_storeManager->getStores();

            foreach ($stores as $store) {
                if (!$onlyActive || $store->isActive()) {
                    $storeCodes[] = $store->getCode();
                }
            }
        } else {
            $storeCodes = [$currentStoreCode];
        }

        return $storeCodes;
    }

    /**
     * Get Scope store.
     *
     * @return string Scope store
     */
    public function getScopeStore()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /**
     * Get API key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_scopeConfig->getValue(self::INTERNAL_SEARCH_CONFIG . '/api_key');
    }

    /**
     * Get Hash ID.
     *
     * @param string $storeCode
     * @return string
     */
    public function getHashId($storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            self::INTERNAL_SEARCH_CONFIG . '/hash_id',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Get request limit for Doofinder search.
     *
     * @param string $storeCode
     * @return string
     */
    public function getSearchRequestLimit($storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            self::INTERNAL_SEARCH_CONFIG . '/request_limit',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Get total limit for Doofinder search.
     *
     * @param string $storeCode
     * @return string
     */
    public function getSearchTotalLimit($storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            self::INTERNAL_SEARCH_CONFIG . '/total_limit',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Check if internal search is enabled.
     *
     * @param string $storeCode
     * @return boolean
     */
    public function isInternalSearchEnabled($storeCode = null)
    {
        $engine = $this->_scopeConfig->getValue(
            self::CATALOG_SEARCH_ENGINE_CONFIG,
            $this->getScopeStore(),
            $storeCode
        );

        return $engine == self::DOOFINDER_SEARCH_ENGINE_NAME;
    }

    /**
     * Check if atomic updates are enabled.
     *
     * @param string $storeCode
     * @return boolean
     */
    public function isAtomicUpdatesEnabled($storeCode = null)
    {
        $engineEnabled = $this->isInternalSearchEnabled($storeCode);

        $atomicUpdatesEnabled = $this->_scopeConfig->getValue(
            self::FEED_SETTINGS_CONFIG . '/atomic_updates_enabled',
            $this->getScopeStore(),
            $storeCode
        );

        return $engineEnabled && $atomicUpdatesEnabled;
    }

    /**
     * Check if should export categories in navigation.
     *
     * @param string $storeCode
     * @return boolean
     */
    public function isExportCategoriesInNavigation($storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            self::FEED_SETTINGS_CONFIG . '/categories_in_navigation',
            $this->getScopeStore(),
            $storeCode
        );
    }
}
