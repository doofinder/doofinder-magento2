<?php

namespace Doofinder\Feed\Service;

use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Helper\Indexation;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\InstallationOptionsStruct;
use Doofinder\Feed\Model\Data\SearchEngineStruct;
use Doofinder\Feed\Model\InstallationRepository;
use Exception;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Integration\Block\Adminhtml\Integration\Tokens as IntegrationTokens;
use Magento\Integration\Model\IntegrationService;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Escaper;

use Magento\Framework\App\Cache\Frontend\Pool as CacheFrontendPool;

class InstallationService
{
    private const CUSTOM_ATTRIBUTES_ENABLED_DEFAULT = ['manufacturer'];

    /** @var StoreConfig */
    private StoreConfig $storeConfig;

    /** @var InstallationRepository */
    private InstallationRepository $installationRepository;

    /** @var IntegrationService */
    private IntegrationService $integrationService;

    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;

    /** @var ManagementClientFactory */
    private ManagementClientFactory $managementClientFactory;

    /** @var AttributeCollectionFactory */
    private AttributeCollectionFactory $attributeCollectionFactory;

    /** @var Escaper */
    private Escaper $escaper;

    /** @var CacheFrontendPool */
    private CacheFrontendPool $cacheFrontendPool;

    /**
     * InstallationService constructor.
     *
     * @param ManagementClientFactory $managementClientFactory
     * @param StoreManagerInterface $storeManager
     * @param StoreConfig $storeConfig
     * @param InstallationRepository $installationRepository
     * @param IntegrationService $integrationService
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param Escaper $escaper
     * @param CacheFrontendPool $cacheFrontendPool
     */
    public function __construct(
        ManagementClientFactory $managementClientFactory,
        StoreManagerInterface $storeManager,
        StoreConfig $storeConfig,
        InstallationRepository $installationRepository,
        IntegrationService $integrationService,
        AttributeCollectionFactory $attributeCollectionFactory,
        Escaper $escaper,
        CacheFrontendPool $cacheFrontendPool
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->installationRepository = $installationRepository;
        $this->integrationService = $integrationService;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->escaper = $escaper;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Generates Doofinder stores for all Magento store groups.
     *
     * This method iterates through all store groups in the Magento store manager,
     * generating a Doofinder store for each group. It handles exceptions for each
     * group individually, allowing the process to continue even if one group fails.
     *
     * @return array An associative array where the keys are store group IDs and the values are either
     *               true (if the store was successfully created) or an error message (if an exception occurred).
     */
    public function generateDoofinderStores()
    {
        $installationResults = [];
        foreach ($this->storeManager->getGroups() as $storeGroup) {
            try {
                $this->generateDoofinderStore($storeGroup);
                $installationResults[$storeGroup->getId()] = true;
            } catch (Exception $e) {
                $installationResults[$storeGroup->getId()] = $e->getMessage();
            }
        }
        $this->setCustomAttributes();
        $this->cleanCache();

        return $installationResults;
    }

    /**
     * Creates and configures a Doofinder store for a given Magento store group.
     *
     * This method handles the integration of a Magento store group with the Doofinder platform.
     * It retrieves necessary configuration data, sends it to Doofinder, and processes the response
     * to store the resulting configuration in Magento's system.
     *
     * Workflow:
     * - Retrieves the website ID, integration ID, and integration token for the store group.
     * - Prepares installation options and fetches installation data for the store group.
     * - Sends the installation data to Doofinder's management client to create the store.
     * - Saves the installation ID and display layer script returned by Doofinder.
     * - Maps and stores the search engine configuration for each store view within the group.
     * - Updates the indexation status for each store view.
     *
     * If an error occurs during the process, it logs the error with the store group's name
     * and rethrows the exception.
     *
     * @param GroupInterface $storeGroup The Magento store group to configure with Doofinder.
     * @return array The response from Doofinder containing installation details.
     * @throws Exception If an error occurs during the store creation process.
     */
    public function generateDoofinderStore(GroupInterface $storeGroup): array
    {
        $websiteId = (int)$storeGroup->getWebsiteId();
        $integrationId = $this->storeConfig->getIntegrationId();
        $integrationToken =
            $this->integrationService->get($integrationId)
            ->getData(IntegrationTokens::DATA_TOKEN);

        $installationOptions = new InstallationOptionsStruct(
            $websiteId,
            $integrationToken
        );

        $installationData =
            $this->installationRepository
            ->getByStoreGroup(
                $storeGroup,
                $installationOptions
            );
        $managementClient =
            $this->managementClientFactory
            ->create(['apiType' => 'dooplugins']);

        $response = $managementClient->createStore($installationData);

        $storeGroupId = (int)$storeGroup->getId();

        $this->storeConfig->setInstallation($response["installation_id"], $storeGroupId);
        $this->storeConfig->setDisplayLayer($response["script"], $storeGroupId);

        // Need to store into database the relation of each store view its related search engine hashid.
        // The response config has the following format:
        // "search_engines":
        //   {"de":{"USD":"024d8eb1caa649775d08f3f69ddf333a"},
        //    "en":{"USD":"c3981a773ac987e5828c94677cda237f"}}
        // We're going to iterate over the sent search engines and get the
        // response based on on language and currency.

        /** @var SearchEngineStruct $searchEngine */
        foreach ($installationData->getSearchEngines() as $searchEngine) {
            $storeId = (int)$searchEngine->getOptions()->getStoreId();
            $currency = $searchEngine->getCurrency();
            $language = $searchEngine->getLanguage();
            $hashid = $response["config"]["search_engines"][$language][$currency];

            $this->storeConfig->setHashId($hashid, $storeId);
            $this->storeConfig->setIndexationStatus(
                ["status" => Indexation::DOOFINDER_INDEX_PROCESS_STATUS_STARTED],
                $storeId
            );
        }
        return $response;
    }

    /**
     * Function to set some custom attributes to enabled by default
     */
    private function setCustomAttributes()
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('is_user_defined', ['eq' => 1]);
        $attributeCollection->addFieldToFilter('attribute_code', ['in' => self::CUSTOM_ATTRIBUTES_ENABLED_DEFAULT]);
        $attributes = [];
        foreach ($attributeCollection as $attribute) {
            $attribute_id = $attribute->getAttributeId();
            $attributes[$attribute_id] = [
                'label' => $this->escaper->escapeHtml($attribute->getFrontendLabel()),
                'code' => $attribute->getAttributeCode(),
                'enabled' => 'on'
            ];
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
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
}
