<?php

declare(strict_types=1);

namespace Doofinder\Feed\Helper;

use Doofinder\Feed\Helper\Indexation;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Catalog\Model\Product;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Escaper;
use Magento\Eav\Model\Config;
use Doofinder\Feed\Errors\NotFound;
use Magento\Backend\Helper\Data;

/**
 * Store config helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreConfig extends AbstractHelper
{
    public const CRON_DISABLED_VALUE = '0 0 * * *';

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
     * Path to search engine settings in config.xml/core_config_data
     */
    public const SEARCH_ENGINE_CONFIG = 'doofinder_config_config/doofinder_search_engine';

    /**
     * Path to integration ID
     */
    public const INTEGRATION_ID_CONFIG = 'doofinder_config_config/doofinder_integration/integration_id';

    /**
     * Path to Sector Value
     */
    public const SECTOR_VALUE_CONFIG = 'doofinder_config_config/doofinder_integration/sector';

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
    public const UPDATE_ON_SAVE_CRON_EXPRESSION = 'doofinder_config_config/update_on_save/cron_expression';

    /**
     * Export product image with given width. Leave empty to use original size
     */
    public const UPDATE_ON_SAVE_IMAGE_SIZE = 'doofinder_config_config/update_on_save/image_size';

    /**
     * Export only categories present in navigation menus
     */
    public const UPDATE_ON_SAVE_CATEGORIES_IN_NAVIGATION =
        'doofinder_config_config/update_on_save/categories_in_navigation';

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
     * Path to search engine indexation status
     */
    public const INDEXATION_STATUS = 'doofinder_config_config/doofinder_search_engines/indexation_status';

    /**
     * Path to endpoint to store doofinder connection data (API key, endpoint api, etc.)
     */
    public const DOOFINDER_CONNECTION = 'doofinderfeed/setup/config';


    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var StoreWebsiteRelationInterface */
    private $storeWebsiteRelation;

    /** @var WriterInterface */
    protected $configWriter;

    /** @var ConfigCollectionFactory */
    protected $configCollectionFactory;

    /**  @var AttributeCollectionFactory */
    protected $attributeCollectionFactory;

    /** @var Indexation  */
    protected $indexationHelper;

    /** @var \Magento\Framework\Escaper */
    protected $escaper;

    /** @var Config */
    private $eavConfig;

    /** @var Data */
    private $backendHelper;

    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;

    /**
     * StoreConfig constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param WriterInterface $configWriter
     * @param ConfigCollectionFactory $configCollectionFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param Indexation $indexationHelper
     * @param Escaper $escaper
     * @param Config $eavConfig
     * @param Data $backendHelper
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        WriterInterface $configWriter,
        ConfigCollectionFactory $configCollectionFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        Indexation $indexationHelper,
        Escaper $escaper,
        Config $eavConfig,
        Data $backendHelper,
        ResourceConnection $resource
    ) {
        $this->storeManager = $storeManager;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->configWriter = $configWriter;
        $this->configCollectionFactory = $configCollectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->indexationHelper = $indexationHelper;
        $this->escaper = $escaper;
        $this->eavConfig = $eavConfig;
        $this->backendHelper = $backendHelper;
        $this->resource = $resource;

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
     * Function to get a store by id
     *
     * @param int|null $storeId
     */
    public function getStoreById($storeId)
    {
        return $this->storeManager->getStore($storeId);
    }

    /**
     * Gets if the Magento is in single store mode
     *
     * @return boolean
     */
    public function isSingleStoreMode(): bool
    {
        return $this->storeManager->isSingleStoreMode();
    }

    /**
     * Get current store based on request parameter or store manager
     *
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
     * Function to get the actual scope based on the request parameter
     *
     * @return mixed[]
     */
    public function getCurrentScope(): array
    {
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $value = null;

        $websiteId = (int) $this->_request->getParam('website', 0);
        $storeId = (int) $this->_request->getParam('store', 0);
        if ($websiteId !== 0) {
            $scope = ScopeInterface::SCOPE_WEBSITES;
            $value = $websiteId;
        } elseif ($storeId !== 0) {
            $scope = ScopeInterface::SCOPE_STORES;
            $value = $storeId;
        }
        return [$scope, $value];
    }

    /**
     * Get current store code based on request parameter or store manager
     *
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
     *
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
     * Get all websites excluding 'default'
     *
     * @return GroupInterface[]
     */
    public function getAllGroups(): array
    {
        return $this->storeManager->getGroups();
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
     * Get stores by store group id
     *
     * @param int $storeId
     * @return StoreInterface[]
     */
    public function getStoreGroupStores(int $storeId): array
    {
        $stores = [];
        $storeIds = $this->getStoreByGroupId($storeId);
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
                return $store->isActive() && $this->getHashId((int)$store->getId()) != null;
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
     * @param string $value
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
     * Set Hashid related with the given store
     *
     * @param string $hashid
     * @param int $storeId
     */
    public function setHashId(string $hashid, int $storeId)
    {
        $this->configWriter->save(self::HASH_ID_CONFIG, $hashid, ScopeInterface::SCOPE_STORES, $storeId);
    }

    /**
     * Set the installation ID
     *
     * @param string $installationId
     * @param int $storeGroupId
     */
    public function setInstallation(string $installationId, int $storeGroupId)
    {
        $this->configWriter->save(
            self::DISPLAY_LAYER_INSTALLATION_ID,
            $installationId,
            ScopeInterface::SCOPE_GROUP,
            $storeGroupId
        );
    }

    /**
     * Retrieve the installation ID for a given store group.
     *
     * @param int $storeGroupId
     * @return string|null
     */
    public function getInstallationId(int $storeGroupId): ?string
    {
        return $this->getValueFromConfig(StoreConfig::DISPLAY_LAYER_INSTALLATION_ID, ScopeInterface::SCOPE_GROUP, $storeGroupId);
    }

    /**
     * Get display layer.
     *
     * @return string|null
     */
    public function getDisplayLayer(): ?string
    {
        try {
            $storeGroupId = $this->getCurrentStore()->getStoreGroupId();
            $displayLayerScript = $this->getValueFromConfig(
                self::DISPLAY_LAYER_SCRIPT_CONFIG,
                ScopeInterface::SCOPE_GROUP,
                (int)$storeGroupId
            );

            if (!empty($displayLayerScript) && 1 !== preg_match('/dfLayerOptions/', $displayLayerScript) &&
            1 !== preg_match('/doofinderApp/', $displayLayerScript)) {
                $store = $this->getCurrentStore();
                $currency = $store->getCurrentCurrency()->getCode();
                $language_country = $this->getLanguageFromStore($store);
                $lang_parts = explode('-', $language_country);
                $language = $lang_parts[0];

                $singleScriptAdditionalConfig = <<<EOT
                    <script>
                        (function(w, k) {w[k] = window[k] || 
                        function () { (window[k].q = window[k].q || []).push(arguments) }})(window, "doofinderApp")

                        doofinderApp("config", "language", "$language")
                        doofinderApp("config", "currency", "$currency")
                    </script>

                EOT;

                $displayLayerScript = $singleScriptAdditionalConfig . $displayLayerScript;
            } elseif (!empty($displayLayerScript) && 1 === preg_match('/dfLayerOptions/', $displayLayerScript)) {
                $locale = $this->getLanguageFromStore($this->getCurrentStore());
                $currency = $this->getCurrentStore()->getCurrentCurrency()->getCode();
                $displayLayerScript = $this->includeLocaleAndCurrency($displayLayerScript, $locale, $currency);
            }
        } catch (\Exception $e) {
            $displayLayerScript = null;
        }

        return $displayLayerScript;
    }

    /**
     * Set display layer
     *
     * @param string $script
     * @param int $storeGroupId
     */
    public function setDisplayLayer(string $script, int $storeGroupId)
    {
        $this->configWriter->save(
            self::DISPLAY_LAYER_SCRIPT_CONFIG,
            $script,
            ScopeInterface::SCOPE_GROUP,
            $storeGroupId
        );
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
        return str_replace("_", "-", $this->getStoreLocaleCode($store));
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
        ) ?? (getenv("DOOFINDER_ADMIN_URL") ?: "https://admin.doofinder.com") . '/plugins/login/magento2';
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
        ) ?? (getenv("DOOFINDER_ADMIN_URL") ?: "https://admin.doofinder.com") . '/plugins/signup/magento2';
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
     * Get Doofinder config (API Token, endpoint, etc.)
     *
     * @return string
     */
    public function getDoofinderConnectUrl(): string
    {
        $host = parse_url($this->backendHelper->getUrl(), PHP_URL_HOST);
        $scheme = parse_url($this->backendHelper->getUrl(), PHP_URL_SCHEME);
        $port = parse_url($this->backendHelper->getUrl(), PHP_URL_PORT);
        $port = empty($port) ? '' : ":$port";
        return sprintf('%1$s://%2$s%3$s/%4$s', $scheme, $host, $port, self::DOOFINDER_CONNECTION);
    }

    /**
     * Get update on schedule configuration value
     *
     * @return bool
     */
    public function isUpdateOnSave(): bool
    {
        $updateOnSchedule = $this->scopeConfig->getValue(
            self::UPDATE_ON_SAVE_CRON_EXPRESSION,
            ScopeInterface::SCOPE_STORE
        );
        return $updateOnSchedule !== self::CRON_DISABLED_VALUE;
    }

    /**
     * Gets image size defined by the user
     *
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
     * @param bool $callback
     * @param int $storeId
     *
     * @throws NoSuchEntityException
     */
    public function setGlobalDisplayLayerEnabled(bool $callback, int $storeId)
    {
        $this->configWriter->save(self::INDICE_CALLBACK, ($callback ? 1 : 0), ScopeInterface::SCOPE_STORES, $storeId);
        $enabled = 1;
        foreach ($this->getAllStores() as $store) {
            if ($this->getValueFromConfig(
                self::INDICE_CALLBACK,
                ScopeInterface::SCOPE_STORES,
                (int)$store->getId()
            ) == 0) {
                $enabled = 0;
                break;
            }
        }
        $this->setDisplayLayerEnabled($enabled);
    }

    /**
     * Gets Doofinder attributes to be merged later
     *
     * @param int|null $storeId
     * @return mixed[]
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
    public function getValueFromConfig(
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
     * @param int|null $id
     * @param string|null $scope
     * @return mixed[]
     */
    public function getCustomAttributes(?int $id = null, ?string $scope = ScopeInterface::SCOPE_STORES): array
    {
        if ($id === null) {
            list($scope, $id) = $this->getCurrentScope();
        }

        $customAttributes = $this->scopeConfig->getValue(self::CUSTOM_ATTRIBUTES, $scope, $id);

        $saved = [];
        if ($customAttributes && is_array($customAttributes)) {
            foreach ($customAttributes as $rowId => $row) {
                if (!isset($saved[$rowId])) {
                    $saved[$rowId] = $row;
                }
            }
        }

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('is_user_defined', ['eq' => 1]);
        $attributes = [];
        foreach ($attributeCollection as $attributeTmp) {
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeTmp->getAttributeId());
            if (!$attribute->getIsVisible()) {
                continue;
            }
            $attribute_id = $attribute->getAttributeId();
            $attributes[$attribute_id] = [
                'code'    => $attribute->getAttributeCode(),
                'label'   => $this->escaper->escapeHtml($attribute->getFrontendLabel())
            ];

            $enabled = isset($saved[$attribute_id]['enabled']) && $saved[$attribute_id]['enabled'];
            $attributes[$attribute_id]['enabled'] = $enabled;
        }
        $attribute_keys = array_keys($attributes);
        array_multisort(array_column($attributes, 'label'), SORT_ASC, $attributes, $attribute_keys);
        $attributes = array_combine($attribute_keys, $attributes);
        return $attributes;
    }

    /**
     * Function to set custom attributes
     *
     * @param string $customAttributes
     */
    public function setCustomAttributes(string $customAttributes)
    {
        $this->configWriter->save(self::CUSTOM_ATTRIBUTES, $customAttributes);
    }

    /**
     * Function to get the indexation status of a given search engine
     *
     * @param int $storeId
     */
    public function getIndexationStatus(int $storeId): array
    {
        $status = $this->scopeConfig->getValue(self::INDEXATION_STATUS, ScopeInterface::SCOPE_STORES, $storeId);

        if ($status == null) {
            throw new NotFound('There is not a valid indexation status for the current store.');
        }

        return json_decode($status, true);
    }

    /**
     * Function to update the indexation status of a given search engine
     *
     * @param array $status
     * @param int $storeId
     */
    public function setIndexationStatus(array $status, int $storeId)
    {
        $status = $this->indexationHelper->sanitizeProcessTaskStatus($status);
        $status = json_encode($status);
        $this->configWriter->save(self::INDEXATION_STATUS, $status, ScopeInterface::SCOPE_STORES, $storeId);
    }

    /**
     * Function to include the locale and the currency into the script.
     *
     * IMPORTANT NOTE: Once the single script is released, this method
     * will become deprecated and it will be removed soon.
     *
     * The following entries are covered:
     *    const dfLayerOptions = {
     *      installationId: '4aa94cbd-e2a0-44db-b1d2-f0817ad2a97d',
     *      zone: 'eu1',
     *      currency: 'USD',
     *      language: 'fr-FR'
     *    };
     *
     *    const dfLayerOptions = {
     *      installationId: '4aa94cbd-e2a0-44db-b1d2-f0817ad2a97d',
     *      zone: 'eu1',
     *      //currency: 'USD',
     *      //language: 'fr-FR'
     *    };
     *
     *    const dfLayerOptions = {
     *      installationId: '4aa94cbd-e2a0-44db-b1d2-f0817ad2a97d',
     *      zone: 'eu1'
     *    };
     *
     * @param string $liveLayerScript
     * @param string $locale
     * @param string $currency
     *
     * @return string
     *    const dfLayerOptions = {
     *      installationId: '4aa94cbd-e2a0-44db-b1d2-f0817ad2a97d',
     *      zone: 'eu1',
     *      currency: 'USD',
     *      language: 'fr-FR'
     *    };
     */
    private function includeLocaleAndCurrency($liveLayerScript, $locale, $currency): string
    {
        if (strpos($liveLayerScript, 'language:') !== false) {
            $liveLayerScript = preg_replace("/(\/\/\s*)?(language:)(.*?)(\n|,)/m", "$2 '$locale'$4", $liveLayerScript);
        } else {
            $pos = strpos($liveLayerScript, "{");
            $liveLayerScript = substr_replace($liveLayerScript, "\r\n\tlanguage: '$locale',", $pos + 1, 0);
        }

        if (strpos($liveLayerScript, 'currency:') !== false) {
            $liveLayerScript = preg_replace(
                "/(\/\/\s*)?(currency:)(.*?)(\n|,)/m",
                "$2 '$currency'$4",
                $liveLayerScript
            );
        } else {
            $pos = strpos($liveLayerScript, "{");
            $liveLayerScript = substr_replace($liveLayerScript, "\r\n\tcurrency: '$currency',", $pos + 1, 0);
        }

        return $liveLayerScript;
    }

    /**
     * Get stores by store_group id
     *
     * @param int $storeGroupId
     * @return mixed[]
     */
    private function getStoreByGroupId($storeGroupId)
    {
        $connection = $this->resource->getConnection();
        $storeViewTable = $this->resource->getTableName('store');
        $storeSelect = $connection->select()->from($storeViewTable, ['store_id'])->where(
            'group_id = ?',
            $storeGroupId
        );
        return $connection->fetchCol($storeSelect);
    }
}
