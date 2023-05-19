<?php

namespace Doofinder\Feed\Model;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\ModuleStruct;
use Doofinder\Feed\Model\Data\StoreStruct;
use Doofinder\Feed\Model\Data\WebsiteStruct;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModuleData {

    const MODULE_PACKAGE_NAME = 'Doofinder_Feed';

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
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ComponentRegistrarInterface $componentRegistrar,
        StoreConfig $storeConfig,
        ProductMetadataInterface $productMetadata,
        ReadFactory $readFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->componentRegistrar = $componentRegistrar;
        $this->storeConfig = $storeConfig;
        $this->productMetadata = $productMetadata;
        $this->readFactory = $readFactory;
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * @inheritDoc
     */
    public function get()
    {
        return json_encode(new ModuleStruct(
            $this->getModuleVersion(),
            $this->getMagentoVersion(),
            $this->getWebsiteStructures()
        ));
    }

    private function getModuleVersion()
    {
        $path = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            self::MODULE_PACKAGE_NAME
        );
        $directoryRead = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile('composer.json');
        return json_decode($composerJsonData)->version;
    }

    private function getMagentoVersion()
    {
        return $this->productMetadata->getEdition() . ': ' . $this->productMetadata->getVersion();
    }
    
    private function getWebsiteStructures()
    {
        $websiteStructs = [];
        foreach ($this->storeConfig->getAllWebsites() as $website) {
            $storeStructs = [];
            $websiteId = $website->getId();
            foreach ($this->storeConfig->getWebsiteStores($websiteId) as $store) {
                $storeCode = $store->getCode();
                $storeStructs[] = new StoreStruct(
                    $store->getId(),
                    $storeCode,
                    $this->storeConfig->getLanguageFromStore($store),
                    $store->getCurrentCurrencyCode(),
                    $this->getInstallationId($storeCode),

                );
            }
            $websiteStructs[] = new WebsiteStruct(
                $websiteId,
                $website->getName(),
                $website->getCode(),
                $storeStructs
            );
        }
        return $websiteStructs;
    }

    private function getInstallationId($storeCode)
    {
        return $this->scopeConfig->getValue(
            'doofinder_config_config/doofinder_layer/installation_id',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}