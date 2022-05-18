<?php

declare(strict_types=1);

namespace Doofinder\Feed\Helper;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Catalog\Model\Product;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Escaper;
use Magento\Eav\Model\Config;

/**
 * Store config helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreConfig extends AbstractHelper
{
    /**
     * URL to make the doofinder requests
     */
    public const URL = 'https://app.doofinder.com';

    /**
     * Path to account settings in config.xml/core_config_data
     */
    public const ACCOUNT_CONFIG = 'doofinder_config_config/doofinder_account';

    /**
     * Path to account settings for login endpoint
     */
    public const LOGIN_ENDPOINT = 'doofinder_config_config/doofinder_integration/login_endpoint';

    /**
     * Path to account settings for signup endpoint
     */
    public const SIGNUP_ENDPOINT = 'doofinder_config_config/doofinder_integration/signup_endpoint';

    public const INSTALLING_LOOP_STATUS = 'doofinder_config_config/installing_loop/status';

    /**
     * Path to display layer create endpoint
     */
    public const DISPLAY_LAYER_CREATE_ENDPOINT = 'doofinder_config_config/doofinder_integration/display_layer_create_endpoint';

    /**
     * Path to display layer create endpoint
     */
    public const DISPLAY_LAYER_STATE_ENDPOINT = 'doofinder_config_config/doofinder_integration/display_layer_state_endpoint';

    /**
     * Path to search engine settings in config.xml/core_config_data
     */
    public const SEARCH_ENGINE_CONFIG = 'doofinder_config_config/doofinder_search_engine';

    /**
     * Path to integration ID
     */
    public const INTEGRATION_ID_CONFIG = 'doofinder_config_config/doofinder_integration/integration_id';

    /**
     * Path to the API KEY value
     */
    public const API_KEY = 'doofinder_config_config/doofinder_account/api_key';

    /**
     * Path to integration ID in config.xml/core_config_data
     */
    public const HASH_ID_CONFIG = 'doofinder_config_config/doofinder_layer/hash_id';

    public const INDICE_CALLBACK = 'doofinder_config_config/doofinder_layer/indice_callback';

    public const DISPLAY_LAYER_ENABLED = 'doofinder_config_config/doofinder_layer/doofinder_layer_enabled';

    public const DISPLAY_LAYER_INSTALLATION_ID = 'doofinder_config_config/doofinder_layer/installation_id';

    public const DISPLAY_LAYER_SCRIPT_CONFIG = 'doofinder_config_config/doofinder_layer/script';

    /**
     * Path to integration ID
     */
    public const UPDATE_ON_SAVE = 'doofinder_config_config/update_on_save/enabled';

    /**
     * Export product prices config path
     */
    public const UPDATE_ON_SAVE_EXPORT_PRODUCT_PRICES = 'doofinder_config_config/update_on_save/export_product_prices';

    /**
     * Export product image with given width. Leave empty to use original size
     */
    public const UPDATE_ON_SAVE_IMAGE_SIZE = 'doofinder_config_config/update_on_save/image_size';

    /**
     * Export only categories present in navigation menus
     */
    public const UPDATE_ON_SAVE_CATEGORIES_IN_NAVIGATION = 'doofinder_config_config/update_on_save/categories_in_navigation';

    /**
     * Path to catalog search engine setting
     */
    public const CATALOG_SEARCH_ENGINE_CONFIG = 'catalog/search/engine';

    /**
     * Doofinder search engine name used as a search engine code
     */
    public const DOOFINDER_SEARCH_ENGINE_NAME = 'doofinder';

    /**
     * Doofinder integration default name
     */
    public const DOOFINDER_INTEGRATION_NAME = 'Doofinder Integration';

    /**
     * Path to administration email
     */
    public const ADMIN_EMAIL_CONFIG = 'trans_email/ident_support/email';

    /**
     * Path to custom attributes to be indexed
     */
    public const CUSTOM_ATTRIBUTES = 'doofinder_config_config/doofinder_custom_attributes/custom_attributes';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ConfigCollectionFactory
     */
    protected $configCollectionFactory;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Eav config
     *
     * @var Config
     */
    private $eavConfig;

    /**
     * StoreConfig constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param WriterInterface $configWriter
     * @param ConfigCollectionFactory $configCollectionFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param Escaper $escaper
     * @param Config $eavConfig
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        WriterInterface $configWriter,
        ConfigCollectionFactory $configCollectionFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        Escaper $escaper,
        Config $eavConfig
    ) {
        $this->storeManager = $storeManager;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->configWriter = $configWriter;
        $this->configCollectionFactory = $configCollectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->escaper = $escaper;
        $this->eavConfig = $eavConfig;

        parent::__construct($context);
    }

    /**
     * Get store code.
     *
     * @param string|null $store
     *
     * @return string Store code.
     * @throws NoSuchEntityException
     */
    public function getStoreCode(?string $store = null): string
    {
        return $this->storeManager->getStore($store)->getCode();
    }

    /**
     * @return boolean
     */
    public function isSingleStoreMode(): bool
    {
        return $this->storeManager->isSingleStoreMode();
    }

    /**
     * Get current store based on request parameter or store manager
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getCurrentStore(): StoreInterface
    {
        if ($storeId = $this->_request->getParam('store')) {
            return $this->storeManager->getStore($storeId);
        }
        return $this->storeManager->getStore();
    }

    /**
     * Get current store code based on request parameter or store manager
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrentStoreCode(): string
    {
        return $this->getCurrentStore()->getCode();
    }

    /**
     * Get current store ID based on request parameter or store manager
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getCurrentStoreId(): int
    {
        return (int)$this->getCurrentStore()->getId();
    }

    /**
     * Check if current operation is a save action
     * @return boolean
     */
    public function isSaveAction(): bool
    {
        return $this->_request->getActionName() == 'save';
    }

    /**
     * Get active/all store codes
     *
     * @param boolean $onlyActive
     * @param boolean $all
     *
     * @return string[]
     * @throws NoSuchEntityException
     */
    public function getStoreCodes(?bool $onlyActive = true, ?bool $all = false): array
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
     * Get all websites excluding 'default'
     *
     * @return WebsiteInterface[]
     */
    public function getAllWebsites(): array
    {
        return $this->storeManager->getWebsites();
    }

    /**
     * Get website stores by website id
     *
     * @param int $websiteId
     * @return StoreInterface[]
     */
    public function getWebsiteStores(int $websiteId): array
    {
        $stores = [];
        $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
        foreach ($storeIds as $storeId) {
            try {
                $stores[] = $this->storeManager->getStore($storeId);
            } catch (NoSuchEntityException $e) {
                $this->_logger->error($e->getMessage());
            }
        }

        return $stores;
    }

    /**
     * Returns all store views available within current website.
     *
     * @param boolean $onlyActive Whether only active store views should be returned.
     *
     * @return StoreInterface[]
     * @throws NoSuchEntityException
     */
    public function getAllStores(?bool $onlyActive = true): array
    {
        $stores = [];
        if ($websiteId = $this->_request->getParam('website')) {
            try {
                $stores = $this->getWebsiteStores((int)$websiteId);
            } catch (NoSuchEntityException $e) {
                $stores = [];
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
     * Get API key.
     *
     * @return string
     */
    public function getApiKey(): ?string
    {
        return $this->getValueFromConfig(self::API_KEY);
    }

    /**
     * Set API key.
     *
     */
    public function setApiKey($value)
    {
        $this->configWriter->save(self::API_KEY, $value);
    }

    /**
     * Get Hash ID.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getHashId(?int $storeId = null): ?string
    {
        return $this->getValueFromConfig(
            self::HASH_ID_CONFIG,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * Get display layer.
     *
     * @return string|null
     */
    public function getDisplayLayer(): ?string
    {
        try {
            $websiteId = $this->getCurrentStore()->getWebsiteId();
            $displayLayerScript = $this->getValueFromConfig(self::DISPLAY_LAYER_SCRIPT_CONFIG, ScopeInterface::SCOPE_WEBSITES, (int)$websiteId);
        } catch (\Exception $e) {
            $displayLayerScript = null;
        }

        return $displayLayerScript;
    }

    /**
     * Get Scope store.
     *
     * @return string Scope store
     */
    public function getScopeStore(): string
    {
        return ScopeInterface::SCOPE_STORE;
    }

    /**
     * Get store locale code from system config
     *
     * @param StoreInterface $store
     * @return string
     */
    public function getStoreLocaleCode(StoreInterface $store): string
    {
        return $this->scopeConfig->getValue(
            Custom::XML_PATH_GENERAL_LOCALE_CODE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Get language code from store
     *
     * @param StoreInterface $store
     * @return string
     */
    public function getLanguageFromStore(StoreInterface $store): string
    {
        $localeCode = explode('_', $this->getStoreLocaleCode($store));

        return $localeCode[0];
    }

    /**
     * Check if internal search is enabled.
     *
     * @return boolean
     */
    public function isInternalSearchEnabled(): bool
    {
        $engine = $this->scopeConfig->getValue(
            self::CATALOG_SEARCH_ENGINE_CONFIG,
            $this->getScopeStore(),
            null
        );

        return $engine == self::DOOFINDER_SEARCH_ENGINE_NAME;
    }

    /**
     * Get the integration id
     *
     * @return string
     */
    public function getIntegrationId(): ?string
    {
        // We avoid cache issues using directly a collection
        $configCollection = $this->configCollectionFactory->create();
        $configCollection->addFieldToFilter("path", ['eq' => self::INTEGRATION_ID_CONFIG]);
        if ($configCollection->count() > 0) {
            if (!empty($configCollection->getFirstItem()->getData()['value'])) {
                return $configCollection->getFirstItem()->getData()['value'];
            }
        }

        return null;
    }

    /**
     * Get the login endpoint
     *
     * @return string
     */
    public function getLoginEndpoint(): string
    {
        return $this->scopeConfig->getValue(
            self::LOGIN_ENDPOINT,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            null
        ) ?? self::URL.'/plugins/login/magento2';
    }

    /**
     * Get the signup endpoint
     *
     * @return string
     */
    public function getSignupEndpoint(): string
    {
        return $this->scopeConfig->getValue(
            self::SIGNUP_ENDPOINT,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            null
        ) ?? self::URL.'/plugins/signup/magento2';
    }

    /**
     * Get display layer create endpoint
     *
     * @return string
     */
    public function getDisplayLayerCreateEndpoint(): string
    {
        return $this->getValueFromConfig(self::DISPLAY_LAYER_CREATE_ENDPOINT)
            ?? self::URL.'/plugins/script/magento2';
    }

    /**
     * Get display layer state endpoint
     *
     * @return string
     */
    public function getDisplayLayerStateEndpoint(): string
    {
        return $this->getValueFromConfig(self::DISPLAY_LAYER_STATE_ENDPOINT)
            ?? self::URL.'/plugins/state/magento2';
    }

    /**
     * Get administration email
     *
     * @return string
     */
    public function getEmailAdmin(): string
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_EMAIL_CONFIG,
            $this->getScopeStore(),
            null
        );
    }

    /**
     * Get default integration name
     *
     * @return string
     */
    public function getIntegrationName(): string
    {
        return self::DOOFINDER_INTEGRATION_NAME;
    }

    /**
     * Get update on save configuration value
     *
     * @return bool
     */
    public function isUpdateOnSave(): bool
    {
        return (bool)$this->getValueFromConfig(self::UPDATE_ON_SAVE);
    }

    /**
     * @param integer|null $storeId
     * @return string
     */
    public function getPriceTaxMode(?int $storeId): ?string
    {
        return $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if it should export product prices.
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isExportProductPrices(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::UPDATE_ON_SAVE_EXPORT_PRODUCT_PRICES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getImageSize(?int $storeId = null): ?string
    {
        /* get default image size */
        return $this->scopeConfig->getValue(
            self::UPDATE_ON_SAVE_IMAGE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if it should export categories in navigation.
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isExportCategoriesInNavigation(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::UPDATE_ON_SAVE_CATEGORIES_IN_NAVIGATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get initial setup installing loop status
     *
     * @return int
     */
    public function getInstallingLoopStatus(): int
    {
        return (int)$this->getValueFromConfig(self::INSTALLING_LOOP_STATUS);
    }

    /**
     * Set initial setup installing loop status
     *
     * @param int $value
     *
     * @return void
     */
    public function setInstallingLoopStatus(int $value)
    {
        $this->configWriter->save(self::INSTALLING_LOOP_STATUS, $value);
    }

    /**
     * Get display layer enabled
     *
     * @return int
     */
    public function getDisplayLayerEnabled(): int
    {
        return (int)$this->getValueFromConfig(self::DISPLAY_LAYER_ENABLED);
    }

    /**
     * Set display layer enabled
     *
     * @param int $value
     */
    public function setDisplayLayerEnabled(int $value)
    {
        $this->configWriter->save(self::DISPLAY_LAYER_ENABLED, $value);
    }

    /**
     * Enable display layer if all indexes callback are true
     *
     * @param bool $enabled
     * @param int $storeId
     *
     * @throws NoSuchEntityException
     */
    public function setGlobalDisplayLayerEnabled(bool $callback, int $storeId)
    {
        $this->configWriter->save(self::INDICE_CALLBACK, ($callback ? 1 : 0), ScopeInterface::SCOPE_STORES, $storeId);
        $enabled = 1;
        foreach ($this->getAllStores() as $store) {
            if ($this->getValueFromConfig(self::INDICE_CALLBACK, ScopeInterface::SCOPE_STORES, (int)$store->getId()) == 0) {
                $enabled = 0;
            }
        }
        $this->setDisplayLayerEnabled($enabled);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getDoofinderAttributes(?int $storeId = null): array
    {
        return [
            'df_grouping_id' => 'df_grouping_id',
        ];
    }

    /**
     * Avoid cache issues using directly a collection
     *
     * @param string $path
     * @param string|null $scope
     * @param int|null $scopeId
     * @return string|null
     */
    private function getValueFromConfig(
        string $path,
        ?string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?int $scopeId = null
    ): ?string {
        $value = null;
        $configCollection = $this->configCollectionFactory->create();
        $configCollection->addFieldToFilter('path', ['eq' => $path]);
        if ($scopeId) {
            $configCollection->addFieldToFilter('scope', ['eq' => $scope]);
            $configCollection->addFieldToFilter('scope_id', ['eq' => $scopeId]);
        }
        if ($configCollection->count() > 0) {
            $data = $configCollection->getFirstItem()->getData();
            $value = !empty($data['value']) ? $data['value'] : null;
        }

        return $value;
    }

    /**
     * Return array of custom attributes
     *
     * @return Array
     */
    public function getCustomAttributes(?int $storeId = null): array
    {
        $custom_attributes = $this->scopeConfig->getValue(self::CUSTOM_ATTRIBUTES, ScopeInterface::SCOPE_STORE, $storeId);
        $custom_attributes = ($custom_attributes) ? \Zend_Json::decode($custom_attributes) : null;
        $saved = [];
        if ($custom_attributes && is_array($custom_attributes)) {
            foreach ($custom_attributes as $rowId => $row) {
                if (!isset($saved[$rowId])) {
                    $saved[$rowId] = $row;
                }
            }
        }

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('is_user_defined',['eq' => 1]);
        $attributes = [];
        foreach ($attributeCollection as $attributeTmp) {
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeTmp->getAttributeId());
            if (!$attribute->getIsSearchable()){
                continue;
            }
            $attribute_id = $attribute->getAttributeId();
            $attributes[$attribute_id] = [
                'code'    => $attribute->getAttributeCode(),
                'label'   => $this->escaper->escapeHtml($attribute->getFrontendLabel())
            ];

            $attributes[$attribute_id]['enabled'] = isset($saved[$attribute_id]['enabled']) && $saved[$attribute_id]['enabled'];
        }
        return $attributes;
    }
}
