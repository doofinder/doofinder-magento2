<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\Indexation;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Block\Adminhtml\Integration\Tokens;
use Magento\Store\Api\Data\StoreInterface;
use Psr\Log\LoggerInterface;

class CreateStore extends Action implements HttpGetActionInterface
{
    private const CUSTOM_ATTRIBUTES_ENABLED_DEFAULT = ['manufacturer'];

    /** @var AttributeCollectionFactory */
    protected $attributeCollectionFactory;

    /** @var StoreConfig */
    private $storeConfig;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var Escaper */
    private $escaper;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @var IntegrationServiceInterface
     */
    protected $integrationService;

    /** @var Pool */
    protected $cacheFrontendPool;

    /**
     * CreateStore constructor.
     *
     * @param StoreConfig $storeConfig
     * @param JsonFactory $resultJsonFactory
     * @param Escaper $escaper
     * @param UrlInterface $urlInterface
     * @param LoggerInterface $logger
     * @param IntegrationServiceInterface $integrationService
     * @param Context $context
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        StoreConfig $storeConfig,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        UrlInterface $urlInterface,
        LoggerInterface $logger,
        IntegrationServiceInterface $integrationService,
        Context $context,
        AttributeCollectionFactory $attributeCollectionFactory,
        Pool $cacheFrontendPool
    ) {
        $this->storeConfig = $storeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->urlInterface = $urlInterface;
        $this->logger = $logger;
        $this->integrationService = $integrationService;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     *
     * @throws WebapiException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        if ($this->generateDoofinderStores() == true) {
            $resultJson->setData(true);
        } else {
            $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
            $resultJson->setData(false);
        }
        return $resultJson;
    }

    /**
     * Generates and registers Doofinder stores for all Magento store groups.
     *
     * This method iterates through all available store groups and attempts to create
     * a corresponding store configuration on the Doofinder platform. It gathers necessary
     * configuration data including search engine information, store options, site URLs,
     * and language settings.
     *
     * For each store group:
     * - It builds a complete store configuration array required by Doofinder.
     * - Sends the configuration to Doofinder via the helper method `createStore`.
     * - On success, stores the returned installation ID and script into Magento's config.
     * - Maps and saves the returned search engine config for the related stores.
     *
     * If any exception occurs during the process for a specific store group, the method logs
     * the error with the group’s name and continues processing the next group. After processing
     * all groups, it ensures that Doofinder custom attributes are set and the Magento cache is cleaned.
     *
     * @return bool Returns true if all store groups were processed successfully; false if any errors occurred.
     */
    public function generateDoofinderStores()
    {
        $success = true;
        foreach ($this->storeConfig->getAllGroups() as $storeGroup) {
            try {
                $storeGroupId = (int)$storeGroup->getId();
                $websiteId = (int)$storeGroup->getWebsiteId();
                $searchEngineData = $this->generateSearchEngineData($storeGroupId);
                $storeOptions = $this->generateStoreOptions($websiteId);
                $primary_language = $this->storeConfig->getLanguageFromStore($storeGroup->getDefaultStore());

                $storeGroupConfig = [
                    "name" => $storeGroup->getName(),
                    "platform" => "magento2",
                    "primary_language" => $primary_language,
                    "skip_indexation" => false,
                    "sector" => $this->storeConfig->getValueFromConfig(StoreConfig::SECTOR_VALUE_CONFIG),
                    "site_url" => $this->getPrimarySiteUrlInSe(
                        $searchEngineData["searchEngineConfig"],
                        $primary_language
                    ),
                    "search_engines" => $searchEngineData["searchEngineConfig"],
                    "options" => $storeOptions,
                    "query_input" => "#search",
                    "plugin_version" => $this->getModuleVersion()
                ];
                $response = $this->storeConfig->createStore($storeGroupConfig);
                $this->saveInstallationConfig((int)$storeGroupId, $response["installation_id"], $response["script"]);
                $this->saveSearchEngineConfig($searchEngineData["storesConfig"], $response["config"]["search_engines"]);
            } catch (Exception $e) {
                $success = false;
                $this->logger->error('Error creating store for store group "' . $storeGroup->getName() .
                    '". ' . $e->getMessage());
            }
        }
        $this->setCustomAttributes();
        $this->cleanCache();

        return $success;
    }

    /**
     * Generates configuration data for Doofinder search engines based on store group stores.
     *
     * This method gathers the necessary configuration for each store view within a given
     * store group to be used for setting up Doofinder search engines.
     *
     * For each store in the specified store group:
     * - Retrieves the store's language, currency, ID, and base URL.
     * - Builds a configuration array with store metadata including:
     *   - Store name
     *   - Language and currency
     *   - Site URL
     *   - Callback URL for Doofinder indexing
     *   - Indexing options including the REST API endpoint
     * - Populates a second structure mapping languages and currencies to store IDs.
     *
     * The resulting data structure is split into:
     * - `searchEngineConfig`: an array with complete config entries per store.
     * - `storesConfig`: a simplified mapping of [language][currency] to store IDs.
     *
     * This data is typically passed to Doofinder during the creation or update of
     * search engine configurations to enable multi-language/multi-currency support.
     *
     * returns array {
     *    array $searchEngineConfig Array of search engine configuration data per store.
     *    array $storesConfig Language-currency mapped store ID reference.
     * }
     *
     * @param int $storeGroupId The ID of the store group whose stores will be used to generate the configuration.
     * @return mixed[]
     */
    public function generateSearchEngineData($storeGroupId)
    {
        $searchEngineConfig = [];
        $storesConfig = [];

        foreach ($this->storeConfig->getStoreGroupStores($storeGroupId) as $store) {
            $language = $this->storeConfig->getLanguageFromStore($store);
            $currency = strtoupper($store->getCurrentCurrency()->getCode());
            $store_id = $store->getId();
            $base_url = $store->getBaseUrl();

            // store_id field refers to store_view's id.
            $searchEngineConfig[] = [
                "name" => $store->getName(),
                "language" => $language,
                "currency" => $currency,
                "site_url" => $base_url,
                "callback_url" => $base_url . 'doofinderfeed/setup/processCallback?storeId=' . $store_id,
                "options" => [
                    "store_id" => $store_id,
                    "index_url" => $base_url . 'rest/' . $store->getCode() . '/V1/'
                ]
            ];

            $storesConfig[$language][$currency] = (int)$store_id;
        }
        return [
            "searchEngineConfig" => $searchEngineConfig,
            "storesConfig" => $storesConfig
        ];
    }

    /**
     * Generates the additional options required for retrieving later the required items
     *
     * @param int $websiteId
     */
    public function generateStoreOptions($websiteId)
    {
        $integrationId = $this->storeConfig->getIntegrationId();
        $integrationToken = $this->integrationService->get($integrationId)->getData(Tokens::DATA_TOKEN);

        return [
            'token' => $integrationToken,
            'website_id' => $websiteId,
        ];
    }

    /**
     * Function to store into the data base the installation id as well as the layer script
     *
     * @param int $storeGroupId
     * @param string $installationId
     * @param string $script
     */
    private function saveInstallationConfig($storeGroupId, $installationId, $script)
    {
        $this->storeConfig->setInstallation($installationId, $storeGroupId);
        $this->storeConfig->setDisplayLayer($script, $storeGroupId);
    }

    /**
     * Function to store into data base the relation of each store view his hashid related
     * The storeConfig variable has the following format:
     * {"en":{"USD":"1"},"de":{"USD":"2"}}
     * The searchEngines variable has the following format:
     * "search_engines":{"de":{"USD":"024d8eb1caa649775d08f3f69ddf333a"},"en":{"USD":"c3981a773ac987e5828c94677cda237f"}}
     * We're going to iterate over the search_engines because there is the data created in doofinder.
     * May occour that some of the data that we've in storeConfig has some invalid parameter and will
     * be bypass during the creation.
     *
     * @param mixed[] $storesConfig
     * @param mixed[] $searchEngines
     */
    private function saveSearchEngineConfig($storesConfig, $searchEngines)
    {
        foreach ($searchEngines as $language => $values) {
            foreach ($values as $currency => $hashid) {
                $storeId = $storesConfig[$language][$currency];
                $this->storeConfig->setHashId($hashid, $storeId);
                $this->setIndexationStatus($storeId);
            }
        }
    }

    /**
     * Function to store the status of the SE indexation.
     *
     * By default we set this value to "STARTED" and will be updated when we receive the callback from doofinder
     *
     * @param int $storeId
     */
    private function setIndexationStatus($storeId)
    {
        $status = ["status" => Indexation::DOOFINDER_INDEX_PROCESS_STATUS_STARTED];
        $this->storeConfig->setIndexationStatus($status, $storeId);
    }

    /**
     * Function to set some custom attributes to enabled by default
     */
    private function setCustomAttributes()
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('is_user_defined', ['eq' => 1]);
        $attributeCollection->addFieldToFilter('attribute_code', ['in' => self::CUSTOM_ATTRIBUTES_ENABLED_DEFAULT]);
        $attributes     = [];
        foreach ($attributeCollection as $attribute) {
            $attribute_id = $attribute->getAttributeId();
            $attributes[$attribute_id] = [
                'label' => $this->escaper->escapeHtml($attribute->getFrontendLabel()),
                'code' => $attribute->getAttributeCode(),
                'enabled' => 'on'
            ];
        }

        $customAttributes = base64_encode(gzcompress(json_encode($attributes)));
        $this->storeConfig->setCustomAttributes($customAttributes);
    }

    /**
     * As we are adding some custom attributes we need to clean the cache to see them into the config panel.
     */
    private function cleanCache()
    {
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     * We obtain the url associated with the main language search_engine
     *
     * @param mixed[] $searchEngines
     * @param string $primaryLanguage
     */
    private function getPrimarySiteUrlInSe($searchEngines, $primaryLanguage)
    {
        $primarySearchEngine = array_values(
            array_filter(
                $searchEngines,
                function ($searchEngine) use ($primaryLanguage) {
                    return $searchEngine["language"] == $primaryLanguage;
                }
            )
        )[0];

        return $primarySearchEngine["site_url"];
    }

    /**
     * Retrieves the current version of the Doofinder_Feed module.
     *
     * This method locates the module's path using the ComponentRegistrar, then reads
     * the `composer.json` file within that path to extract the version defined for
     * the module.
     *
     * Steps:
     * - Uses Magento's ObjectManager to retrieve the `ComponentRegistrarInterface`
     *   and `ReadFactory` to interact with the file system.
     * - Resolves the absolute path to the module using its name.
     * - Verifies the existence of `composer.json` within that module directory.
     * - Parses the JSON content and extracts the `version` property.
     *
     * If the version is defined in the composer file, it is returned. Otherwise,
     * an empty string is returned.
     *
     * @return string The module version as defined in composer.json, or an empty string if not found.
     */
    private function getModuleVersion(): string
    {
        $objectManager = ObjectManager::getInstance();
        $componentReg = $objectManager->get(\Magento\Framework\Component\ComponentRegistrarInterface::class);
        $register = $objectManager->get(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $path = $componentReg->getPath(
            ComponentRegistrar::MODULE,
            'Doofinder_Feed'
        );
        $directoryRead = $register->create($path);
        $composerJsonData = '';
        if ($directoryRead->isFile('composer.json')) {
            $composerJsonData = $directoryRead->readFile('composer.json');
        }
        $data = json_decode($composerJsonData);

        return !empty($data->version) ? $data->version : '';
    }
}
