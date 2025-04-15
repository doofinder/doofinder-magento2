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
use Psr\Log\LoggerInterface;

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

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /** @var CacheFrontendPool */
    private CacheFrontendPool $cacheFrontendPool;

    public function __construct(
        ManagementClientFactory $managementClientFactory,
        StoreManagerInterface $storeManager,
        StoreConfig $storeConfig,
        InstallationRepository $installationRepository,
        IntegrationService $integrationService,
        AttributeCollectionFactory $attributeCollectionFactory,
        Escaper $escaper,
        LoggerInterface $logger,
        CacheFrontendPool $cacheFrontendPool
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->installationRepository = $installationRepository;
        $this->integrationService = $integrationService;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->escaper = $escaper;
        $this->logger = $logger;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }
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

    public function generateDoofinderStore(GroupInterface $storeGroup)
    {
        $success = true;
        try {

            $websiteId = (int)$storeGroup->getWebsiteId();
            $integrationId = $this->storeConfig->getIntegrationId();
            $integrationToken = $this->integrationService->get($integrationId)->getData(IntegrationTokens::DATA_TOKEN);

            $installationOptions = new InstallationOptionsStruct(
                $websiteId,
                $integrationToken
            );

            $installationData = $this->installationRepository->getByStoreGroup($storeGroup, $installationOptions);
            $managementClient = $this->managementClientFactory->create(['apiType' => 'dooplugins']);

            $response = $managementClient->createStore($installationData);

            $storeGroupId = (int)$storeGroup->getId();

            $this->storeConfig->setInstallation($response["installation_id"], $storeGroupId);
            $this->storeConfig->setDisplayLayer($response["script"], $storeGroupId);

            // Need to store into database the relation of each store view its related search engine hashid.
            // The response config has the following format:
            // "search_engines":{"de":{"USD":"024d8eb1caa649775d08f3f69ddf333a"},"en":{"USD":"c3981a773ac987e5828c94677cda237f"}}
            // We're going to iterate over the sent search engines and get the response based on on language and currency.

            /** @var SearchEngineStruct $searchEngine */
            foreach ($installationData->getSearchEngines() as $searchEngine) {
                $storeId = (int)$searchEngine->getOptions()->getStoreId();
                $currency = $searchEngine->getCurrency();
                $language = $searchEngine->getLanguage();
                $hashid = $response["config"]["search_engines"][$language][$currency];

                $this->storeConfig->setHashId($hashid, $storeId);
                $this->storeConfig->setIndexationStatus(["status" => Indexation::DOOFINDER_INDEX_PROCESS_STATUS_STARTED], $storeId);
            }
        } catch (Exception $e) {
            $message = 'Error creating store for store group "' . $storeGroup->getName() . '". ' . $e->getMessage();
            $this->logger->error($message);
            throw new Exception($message);
        }
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
