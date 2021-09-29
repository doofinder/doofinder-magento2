<?php

namespace Doofinder\Feed\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Store config helper
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class StoreConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Name of this module
     */
    const MODULE_NAME = 'Doofinder_Feed';

    /**
     * Path to attributes config in config.xml/core_config_data
     */
    const FEED_ATTRIBUTES_CONFIG = 'doofinder_config_index/feed_attributes';

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
     * Path to statistics settings in config.xml/core_config_data
     */
    const STATS_CONFIG = 'doofinder_config_config/doofinder_stats';

    /**
     * Path to catalog search engine setting
     */
    const CATALOG_SEARCH_ENGINE_CONFIG = 'catalog/search/engine';

    /**
     * Doofinder search engine name used as a search engine code
     */
    const DOOFINDER_SEARCH_ENGINE_NAME = 'doofinder';

    /**
     * Path to indices update mode
     */
    const INDICES_UPDATE_MODE = 'doofinder_config_config/doofinder_search_engine/doofinder_indices_update_mode';

    /**
     * Doofinder indices update type api as update type code
     */
    const DOOFINDER_INDICES_UPDATE_API = 'api';

    /**
     * Doofinder indices update type feed as update type code
     */
    const DOOFINDER_INDICES_UPDATE_FEED = 'feed';

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
     * StoreConfig constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\Serializer $serializer
     * @param \Doofinder\Feed\Model\StoreWebsiteRelation $storeWebsiteRelation
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\Serializer $serializer,
        \Doofinder\Feed\Model\StoreWebsiteRelation $storeWebsiteRelation
    ) {
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        parent::__construct($context);
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
     * @return boolean
     */
    public function isSingleStoreMode()
    {
        return $this->storeManager->isSingleStoreMode();
    }

    /**
     * Get current store based on request parameter or store manager
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getCurrentStore()
    {
        $storeId = $this->_request->getParam('store');
        if ($storeId) {
            return $this->storeManager->getStore($storeId);
        }
        return $this->storeManager->getStore();
    }

    /**
     * Get current store code based on request parameter or store manager
     * @return string
     */
    public function getCurrentStoreCode()
    {
        return $this->getCurrentStore()->getCode();
    }

    /**
     * Check if current operation is a save action
     * @return boolean
     */
    public function isSaveAction()
    {
        return $this->_request->getActionName() == 'save';
    }

    /**
     * Returns all store views available within current website.
     *
     * @param boolean $onlyActive Whether only active store views should be returned.
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getAllStores($onlyActive = true)
    {
        $stores = [];

        $websiteId = $this->_request->getParam('website');
        if ($websiteId) {
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
        $storeId = $this->_request->getParam('store');
        if (!$all && $storeId) {
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
     * @return string
     */
    public function getSearchServer()
    {
        return $this->scopeConfig->getValue(self::ACCOUNT_CONFIG . '/search_server');
    }

    /**
     * @return string
     */
    public function getManagementServer()
    {
        return $this->scopeConfig->getValue(self::ACCOUNT_CONFIG . '/management_server');
    }

    /**
     * Get value from store config save request. If it's not set, use the default one
     * @return string
     */
    public function getManagementServerFromRequest()
    {
        $params = $this->_request->getParam('groups');
       return  $params['doofinder_account']['fields']['management_server']['value']
            ?? $this->getManagementServer();
         
    }

    /**
     * Get Doofinder attributes that should be indexed
     * @param null|integer $storeId
     * @return array
     */
    public function getDoofinderFields($storeId = null)
    {
        $attributes = [];
        $attributes['brand'] = $this->scopeConfig->getValue(
            self::FEED_ATTRIBUTES_CONFIG . '/brand',
            $this->getScopeStore(),
            $storeId
        );
        $attributes['image_link'] = $this->scopeConfig->getValue(
            self::FEED_ATTRIBUTES_CONFIG . '/image_link',
            $this->getScopeStore(),
            $storeId
        );
        $attributes['mpn'] = $this->scopeConfig->getValue(
            self::FEED_ATTRIBUTES_CONFIG . '/mpn',
            $this->getScopeStore(),
            $storeId
        );
        $attributes['additional_attributes'] = $this->scopeConfig->getValue(
            self::FEED_ATTRIBUTES_CONFIG . '/additional_attributes',
            $this->getScopeStore(),
            $storeId
        );

        $unserialized = $this->serializer->unserialize($attributes['additional_attributes']);
        foreach ($unserialized as $attr) {
            $attributes[$attr['field']] = $attr['additional_attribute'];
        }
        unset($attributes['additional_attributes']);
        return $attributes;
    }

    /**
     * @param string $storeCode
     * @return string
     */
    public function getStoreLanguage($storeCode)
    {
        return $this->scopeConfig->getValue(
            'general/locale/code',
            $this->getScopeStore(),
            $storeCode
        );
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
     * Check if internal search is enabled.
     *
     * @return boolean
     */
    public function isInternalSearchEnabled()
    {
        $engine = $this->scopeConfig->getValue(
            self::CATALOG_SEARCH_ENGINE_CONFIG,
            $this->getScopeStore(),
            null
        );

        return $engine == self::DOOFINDER_SEARCH_ENGINE_NAME;
    }

    /**
     * Check if should export categories in navigation.
     *
     * @param string|null $storeCode
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
     * @param string|null $storeCode
     * @return string
     */
    public function getImageSize($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::FEED_SETTINGS_CONFIG . '/image_size',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Check if should export product prices.
     *
     * @param string|null $storeCode
     * @return boolean
     */
    public function isExportProductPrices($storeCode = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::FEED_SETTINGS_CONFIG . '/export_product_prices',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * @param integer $storeId
     * @return string
     */
    public function getPriceTaxMode($storeId)
    {
        return $this->scopeConfig->getValue(
            self::FEED_SETTINGS_CONFIG . '/price_tax_mode',
            $this->getScopeStore(),
            $storeId
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

    /**
     * Get selector for search result container.
     *
     * @param string|null $storeCode
     * @return string
     */
    public function getSearchResultContainer($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::STATS_CONFIG . '/search_product_container',
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Get selector for search result link.
     *
     * @param string|null $storeCode
     * @return string
     */
    public function getSearchResultLink($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::STATS_CONFIG . '/search_product_link',
            $this->getScopeStore(),
            $storeCode
        );
    }

    public function getUpdateMode($storeCode = null) {
        return $this->scopeConfig->getValue(
            self::INDICES_UPDATE_MODE,
            $this->getScopeStore(),
            $storeCode
        );
    }

    /**
     * Check if update indices by feed is enabled.
     *
     * @return boolean
     */
    public function isUpdateByFeedEnable($storeCode = null)
    {
        return $this->getUpdateMode($storeCode) == self::DOOFINDER_INDICES_UPDATE_FEED;
    }

    /**
     * Check if update indices by api is enabled.
     *
     * @return boolean
     */
    public function isUpdateByApiEnable($storeCode = null)
    {
        return $this->getUpdateMode($storeCode) == self::DOOFINDER_INDICES_UPDATE_API;
    }
}
