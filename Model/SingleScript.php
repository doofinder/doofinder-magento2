<?php

namespace Doofinder\Feed\Model;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\SingleScriptStruct;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SingleScript
{

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;
    
    /**
     * @var StoreConfig
     */
    protected $storeConfig;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param StoreConfig $storeConfig
     * @param ReadFactory $readFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ComponentRegistrarInterface $componentRegistrar,
        StoreConfig $storeConfig,
        ReadFactory $readFactory,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->componentRegistrar = $componentRegistrar;
        $this->storeConfig = $storeConfig;
        $this->readFactory = $readFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }
    
    /**
     * @inheritDoc
     */
    public function replace()
    {
        $singleScriptStruct = new SingleScriptStruct(
            $this->replaceAndGetScripts()
        );
        return [$singleScriptStruct->jsonSerialize()];
    }
    
    private function replaceAndGetScripts(): array
    {
        $scripts = [];
        foreach ($this->storeConfig->getAllWebsites() as $website) {
            $websiteId = $website->getId();
            foreach ($this->storeConfig->getWebsiteStores($websiteId) as $store) {
                $installationId = $this->getInstallationId($store);
                $region = $this->getRegionFromApiKey();
                if (empty($installationId) || empty($region)) {
                    continue;
                }
                
                $singleScript = '<script src="https://' . $region . '-config.doofinder.com/2.x/' . $installationId . '.js" async></script>';

                $storeGroupId = $store->getStoreGroupId();
                $scripts[] = trim($singleScript);
                $this->storeConfig->setDisplayLayer($singleScript, $storeGroupId);
            }
        }

        $this->executeCacheCleaner();

        return $scripts;
    }

    private function getInstallationId(StoreInterface $store): ?string
    {
        $storeGroupId = $store->getStoreGroupId();
        return $this->storeConfig->getValueFromConfig(
            'doofinder_config_config/doofinder_layer/installation_id',
            ScopeInterface::SCOPE_GROUP,
            $storeGroupId
        );
    }

    private function getRegionFromApiKey(): string
    {
        $apiKey = $this->storeConfig->getValueFromConfig('doofinder_config_config/doofinder_account/api_key');
        if (empty($apiKey)) {
            return '';
        }
        $apiKeyParts = explode('-', $apiKey);
        $region = $apiKeyParts[0];

        if (0 === preg_match('/^(us|eu)[0-9]+$/', $region) ) {
            return '';
        }

        return $region;
    }

    /*
     * Possible types:
     * 'config','layout','block_html','collections','reflection',
     * 'db_ddl','eav','config_integration','config_integration_api',
     * 'full_page','translate','config_webservice'
     */
    private function executeCacheCleaner(): void
    {
        try {
            $objectManager = ObjectManager::getInstance();
            $cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
            $type = 'full_page';
            $cacheTypeList->cleanType($type);
        } catch(\Exception $e) {
            $this->logger->error("Error cleaning cache: " . $e->getMessage());
        }
    }
}
