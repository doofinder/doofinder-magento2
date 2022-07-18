<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\SearchEngine;
use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Block\Adminhtml\Integration\Tokens;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class CreateStore extends Action implements HttpGetActionInterface
{
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
        WriterInterface $configWriter,
        StoreConfig $storeConfig,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        UrlInterface $urlInterface,
        LoggerInterface $logger,
        IntegrationServiceInterface $integrationService,
        AttributeCollectionFactory $attributeCollectionFactory,
        Context $context
    ) {
        $this->configWriter = $configWriter;
        $this->storeConfig = $storeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->urlInterface = $urlInterface;
        $this->logger = $logger;
        $this->integrationService = $integrationService;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
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
                $websiteConfig = [
                    "name" => $website->getName(),
                    "platform" => "magento2",
                    "primary_language" => $this->storeConfig->getLanguageFromStore($website->getDefaultStore()),
                    "skip_indexation" => false,
                    "search_engines" => $this->generateSearchEngineData((int)$website->getId())
                ];
                $response = $this->storeConfig->createStore($websiteConfig);
                $this->saveInstallationConfig((int)$website->getId(), $response["installation_id"], $response["script"]);
            } catch (Exception $e) {
                $success = false;
                $this->logger->error('Error creating store for website "' . $website->getName() . '". ' . $e->getMessage());
            }
        }
        return $success;
    }

    public function generateSearchEngineData($websiteID)
    {
        $searchEngineConfig = [];
        foreach ($this->storeConfig->getWebsiteStores($websiteID) as $store) {
            $integrationToken = $this->integrationService
                ->get($this->storeConfig->getIntegrationId())
                ->getData(Tokens::DATA_TOKEN);

            $searchEngineConfig[] = [
                "name" => $store->getName(),
                "language" => $this->storeConfig->getLanguageFromStore($store),
                "currency" => strtoupper($store->getCurrentCurrency()->getCode()),
                "site_url" => $store->getBaseUrl(),
                "datatypes" => [
                    [
                        "name" => "product",
                        "preset" => "product",
                        'datasources' => [
                            [
                                'type' => 'magento2',
                                'options' => [
                                    'url' => $this->urlInterface->getBaseUrl() . 'rest/' . $store->getCode() . '/V1/',
                                    'token' => $integrationToken,
                                    'website_id' => $store->getWebsiteId(),
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        return $searchEngineConfig;
    }

    private function saveInstallationConfig($websiteID, $installationId, $script) 
    {
        $this->configWriter->save(StoreConfig::DISPLAY_LAYER_INSTALLATION_ID, $installationId, ScopeInterface::SCOPE_WEBSITES, $websiteID);
        $this->configWriter->save(StoreConfig::DISPLAY_LAYER_SCRIPT_CONFIG, $script, ScopeInterface::SCOPE_WEBSITES, $websiteID);
    }
}