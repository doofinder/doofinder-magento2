<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\Indexation;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
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

    public function generateDoofinderStores()
    {
        $success = true;
        foreach($this->storeConfig->getAllWebsites() as $website) {
            try {
                $searchEngineData = $this->generateSearchEngineData((int)$website->getId());
                $websiteConfig = [
                    "name" => $website->getName(),
                    "platform" => "magento2",
                    "primary_language" => $this->storeConfig->getLanguageFromStore($website->getDefaultStore()),
                    "skip_indexation" => false,
                    "callback_urls" => $searchEngineData["callbackUrls"],
                    "sector" => $this->storeConfig->getValueFromConfig(StoreConfig::SECTOR_VALUE_CONFIG),
                    "search_engines" => $searchEngineData["searchEngineConfig"],
                    "query_input" => "#search"
                ];
                $response = $this->storeConfig->createStore($websiteConfig);
                $this->saveInstallationConfig((int)$website->getId(), $response["installation_id"], $response["script"]);
                $this->saveSearchEngineConfig($searchEngineData["storesConfig"], $response["search_engines"]);
            } catch (Exception $e) {
                $success = false;
                $this->logger->error('Error creating store for website "' . $website->getName() . '". ' . $e->getMessage());
            }
        }
        $this->setCustomAttributes();
        $this->cleanCache();

        return $success;
    }

    public function generateSearchEngineData($websiteID)
    {
        $searchEngineConfig = [];
        $storesConfig = [];
        $callbackUrls = [];

        foreach ($this->storeConfig->getWebsiteStores($websiteID) as $store) {
            $integrationToken = $this->integrationService->get($this->storeConfig->getIntegrationId())->getData(Tokens::DATA_TOKEN);
            $language = $this->storeConfig->getLanguageFromStore($store);
            $currency = strtoupper($store->getCurrentCurrency()->getCode());

            $searchEngineConfig[] = [
                "name" => $store->getName(),
                "language" => $language,
                "currency" => $currency,
                "site_url" => $store->getBaseUrl(),
                "options" => [
                    'url' => $this->urlInterface->getBaseUrl() . 'rest/' . $store->getCode() . '/V1/',
                    'token' => $integrationToken,
                    'website_id' => $store->getWebsiteId(),
                ],
                "datatypes" => [
                    [
                        "name" => "product",
                        "preset" => "product",
                        'datasources' => [
                            [
                                'type' => 'magento2'
                            ]
                        ]
                    ]
                ]
            ];
            $storesConfig[$language][$currency] = (int)$store->getId();
            $callbackUrls[$language][$currency] = $this->getProcessCallbackUrl($store);
        }
        return ["searchEngineConfig" => $searchEngineConfig, "storesConfig" => $storesConfig, "callbackUrls" => $callbackUrls];
    }

    /**
     * Function to store into the data base the installation id as well as the layer script
     */
    private function saveInstallationConfig($websiteID, $installationId, $script)
    {
        $this->storeConfig->setInstallation($installationId, $websiteID);
        $this->storeConfig->setDisplayLayer($script, $websiteID);
    }

    /**
     * Function to store into data base the relation of each store view his hashid related
     * The storeConfig variable has the following format:
     * {"en":{"USD":"1"},"de":{"USD":"2"}}
     * The searchEngines variable has the following format:
     * "search_engines":{"de":{"USD":"024d8eb1caa649775d08f3f69ddf333a"},"en":{"USD":"c3981a773ac987e5828c94677cda237f"}}
     * We're going to iterate over the search_engines because there is the data created in doofinder. May occour that some
     * of the data that we've in storeConfig has some invalid parameter and will be bypass during the creation.
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
     * By default we set this value to "STARTED" and will be updated when we receive the callback from doofinder
     */
    private function setIndexationStatus($storeId) {
        $status = ["status" => Indexation::DOOFINDER_INDEX_PROCESS_STATUS_STARTED];
        $this->storeConfig->setIndexationStatus($status, $storeId);
    }

    /**
     * Function to set some custom attributes to enabled by default
     */
    private function setCustomAttributes()
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('is_user_defined',['eq' => 1]);
        $attributeCollection->addFieldToFilter('attribute_code',['in' => self::CUSTOM_ATTRIBUTES_ENABLED_DEFAULT]);
        $attributes     = [];
        foreach ($attributeCollection as $attribute) {
            $attribute_id = $attribute->getAttributeId();
            $attributes[$attribute_id] = ['label' => $this->escaper->escapeHtml($attribute->getFrontendLabel()),
                                          'code' => $attribute->getAttributeCode(),
                                          'enabled' => 'on'];
        }

        $customAttributes = \Zend_Json::encode($attributes);
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
     * Get Process Callback URL
     *
     * @param StoreInterface $store
     *
     * @return string
     */
    private function getProcessCallbackUrl(StoreInterface $store): string
    {
        return $store->getBaseUrl() . 'doofinderfeed/setup/processCallback?storeId='.$store->getId();
    }
}
