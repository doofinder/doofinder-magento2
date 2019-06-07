<?php

namespace Doofinder\Feed\Helper;

/**
 * Store config helper
 */
class StoreConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Path to attributes config in config.xml/core_config_data
     */
    const FEED_ATTRIBUTES_CONFIG = 'doofinder_config_index/feed_attributes';

    /**
     * Path to cron config in config.xml/core_config_data
     */
    const FEED_CRON_CONFIG = 'doofinder_config_data_feed/cron_settings';

    /**
     * Path to feed settings in config.xml/core_config_data
     */
    const FEED_SETTINGS_CONFIG = 'doofinder_config_index/feed_settings';

    /**
     * Path to search layer settings in config.xml/core_config_data
     */
    const SEARCH_LAYER_CONFIG = 'doofinder_config_config/doofinder_layer';

    /**
     * Path to search layer settings in config.xml/core_config_data
     */
    const BANNERS_CONFIG = 'doofinder_config_config/doofinder_banners';

    /**
     * Path to account settings in config.xml/core_config_data
     */
    const ACCOUNT_CONFIG = 'doofinder_config_config/doofinder_account';

    /**
     * Path to search engine settings in config.xml/core_config_data
     */
    const SEARCH_ENGINE_CONFIG = 'doofinder_config_config/doofinder_search_engine';

    /**
     * Path to catalog search engine setting
     */
    const CATALOG_SEARCH_ENGINE_CONFIG = 'catalog/search/engine';

    /**
     * Doofinder search engine name
     */
    const DOOFINDER_SEARCH_ENGINE_NAME = 'doofinder';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Helper\Serializer
     */
    private $serializer;

    /**
     * @var \Doofinder\Feed\Model\StoreWebsiteRelation
     */
    private $storeWebsiteRelation;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    private $configCollection;

    /**
     * StoreConfig constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\Serializer $serializer
     * @param \Doofinder\Feed\Model\StoreWebsiteRelation $storeWebsiteRelation
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\Serializer $serializer,
        \Doofinder\Feed\Model\StoreWebsiteRelation $storeWebsiteRelation,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollection
    ) {
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->configCollection = $configCollection;
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
            ['attributes' => $this->scopeConfig->getValue(self::FEED_ATTRIBUTES_CONFIG, $scopeStore, $storeCode)],
            $this->scopeConfig->getValue(self::FEED_CRON_CONFIG, $scopeStore, $storeCode),
            $this->scopeConfig->getValue(self::FEED_SETTINGS_CONFIG, $scopeStore, $storeCode)
        );

        /**
         * @notice 'backend_model' does not process config value
         *          so we need to unserialize value here.
         * @see     MAGETWO-80296
         */
        if (!empty($config['attributes']['additional_attributes'])) {
            $additionalAttributes = [];
            foreach ($this->serializer->unserialize(
                $config['attributes']['additional_attributes']
            ) as $data) {
                $additionalAttributes[$data['field']] = $data['additional_attribute'];
            }

            unset($config['attributes']['additional_attributes']);
            $config['attributes'] = array_merge($config['attributes'], $additionalAttributes);
        }

        /**
         * @notice There is a bug in PHP 7.0.7 and Magento 2.1.3+
         *         which resulted with references in $config array,
         *         so we merge $config array with new value instead
         *         of replacing key with new value.
         */
        $config = array_merge(
            $config,
            ['start_time' => explode(',', $config['start_time'])]
        );

        return $config;
    }

    /**
     * Get store code.
     *
     * @param string $store
     * @return string Store code.
     */
    public function getStoreCode($store = null)
    {
        return $this->storeManager->getStore($store)->getCode();
    }

    /**
     * Get current store code based on request parameter or store manager
     * @return string
     */
    public function getCurrentStoreCode()
    {
        if ($storeId = $this->_request->getParam('store')) {
            return $this->storeManager->getStore($storeId)->getCode();
        }
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * Returns all store views available within current website.
     *
     * @param boolean $onlyActive Whether only active store views should be returned.
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    private function getAllStores($onlyActive = true)
    {
        $stores = [];

        if ($websiteId = $this->_request->getParam('website')) {
            $storeIds = $this->storeWebsiteRelation
                ->getStoreByWebsiteId($websiteId);

            foreach ($storeIds as $storeId) {
                $stores[] = $this->storeManager
                    ->getStore($storeId);
            }
        } else {
            $stores = $this->storeManager
                ->getStores();
        }

        if (!$onlyActive) {
            return $stores;
        }

        return array_filter(
            $stores,
            function ($store) {
                return $store->isActive();
            }
        );
    }

    /**
     * Get active/all store codes
     *
     * @param boolean $onlyActive
     * @param boolean $all
     * @return string[]
     */
    public function getStoreCodes($onlyActive = true, $all = false)
    {
        if (!$all && $storeId = $this->_request->getParam('store')) {
            return [$this->storeManager->getStore($storeId)->getCode()];
        }

        $storeCodes = [];
        $stores = $this->getAllStores($onlyActive);

        foreach ($stores as $store) {
            $storeCodes[] = $store->getCode();
        }

        return $storeCodes;
    }

    /**
     * Returns the store view ID for a store having given store code.
     *
     * @param string $storeCode Code of the store whose ID should be returned.
     *
     * @return mixed
     */
    public function getStoreViewIdByStoreCode($storeCode)
    {
        $stores = $this->getAllStores(false);

        foreach ($stores as $store) {
            if ($store->getCode() === $storeCode) {
                return $store->getId();
            }
        }

        return null;
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
        return $this->scopeConfig->getValue(self::ACCOUNT_CONFIG . '/api_key');
    }

    /**
     * Check if search engine is enabled for store
     *
     * @param string|null $storeCode
     * @return boolean
     */
    public function isStoreSearchEngineEnabled($storeCode = null)
    {
        return (boolean) $this->scopeConfig->getValue(
            self::SEARCH_ENGINE_CONFIG . '/enabled',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Check if search engine is enabled for store with avoid cache
     *
     * @param string|integer $storeCode
     * @return boolean
     */
    public function isStoreSearchEngineEnabledNoCached($storeCode)
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $collection = $this->configCollection->create();
        $collection->addScopeFilter($scope, $storeCode, self::SEARCH_ENGINE_CONFIG);
        foreach ($collection as $item) {
            if ($item->getData('path') === self::SEARCH_ENGINE_CONFIG . '/enabled') {
                $value = $item->getData('value');
                if ($value === null) {
                    return true;
                }

                return (bool) $value;
            }
        }

        return true;
    }

    /**
     * Get Hash ID.
     *
     * @param string $storeCode
     * @return string
     */
    public function getHashId($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::SEARCH_ENGINE_CONFIG . '/hash_id',
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
        return $this->scopeConfig->getValue(
            self::SEARCH_ENGINE_CONFIG . '/request_limit',
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
        $pageLimit = $this->scopeConfig->getValue(
            self::SEARCH_ENGINE_CONFIG . '/page_limit',
            $this->getScopeStore(),
            $storeCode
        );

        return $pageLimit * $this->getSearchRequestLimit($storeCode);
    }

    /**
     * Check if internal search is enabled.
     *
     * @param string $storeCode
     * @return boolean
     */
    public function isInternalSearchEnabled($storeCode = null)
    {
        $engine = $this->scopeConfig->getValue(
            self::CATALOG_SEARCH_ENGINE_CONFIG,
            $this->getScopeStore(),
            $storeCode
        );

        return $engine == self::DOOFINDER_SEARCH_ENGINE_NAME;
    }

    /**
     * Check if atomic updates are enabled.
     *
     * Delayed product updates take precedence over atomic updates. For atomic updates
     * to work, Doofinder must be set as internal search engine and delayed updates
     * must be disabled.
     *
     * @param string $storeCode
     * @return boolean
     */
    public function isAtomicUpdatesEnabled($storeCode = null)
    {
        if ($this->isInternalSearchEnabled($storeCode)) {
            return false;
        }

        if ($this->isDelayedUpdatesEnabled($storeCode)) {
            return false;
        }

        return $this->scopeConfig->getValue(
            self::FEED_SETTINGS_CONFIG . '/atomic_updates_enabled',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Check, whether delayed product updates are enabled and active.
     *
     * For delayed products updates to work, Doofinder must be set as internal search engine.
     * If delayed updates are enabled, atomic updates are not invocated.
     *
     * @param string $storeCode
     *
     * @return boolean True if Cron updates are enabled.
     */
    public function isDelayedUpdatesEnabled($storeCode = null)
    {
        if (!$this->isInternalSearchEnabled($storeCode)) {
            return false;
        }

        return $this->scopeConfig
            ->getValue(
                self::FEED_SETTINGS_CONFIG . '/cron_updates_enabled',
                $this->getScopeStore(),
                $storeCode
            );
    }

    /**
     * Check if should export categories in navigation.
     *
     * @param string $storeCode
     * @return boolean
     */
    public function isExportCategoriesInNavigation($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::FEED_SETTINGS_CONFIG . '/categories_in_navigation',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Get search layer script.
     *
     * @param string $storeCode
     * @return string
     */
    public function getSearchLayerScript($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::SEARCH_LAYER_CONFIG . '/script',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Check if banners display is enabled.
     *
     * @param string|null $storeCode
     * @return string
     */
    public function isBannersDisplayEnabled($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::BANNERS_CONFIG . '/enabled',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Get banner placement location.
     *
     * @param string|null $storeCode
     * @return string
     */
    public function getBannerInsertionPoint($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::BANNERS_CONFIG . '/insertion_point',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Get banner insertion method.
     *
     * @param string|null $storeCode
     * @return string
     */
    public function getBannerInsertionMethod($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::BANNERS_CONFIG . '/insertion_method',
            $this->getScopeStore(),
            $storeCode
        );
    }
}
