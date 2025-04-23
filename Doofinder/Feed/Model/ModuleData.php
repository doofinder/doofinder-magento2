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
 * Class ModuleData
 *
 * Provides metadata and configuration information related to the Doofinder module and the Magento installation.
 */
class ModuleData
{
    private const MODULE_PACKAGE_NAME = 'Doofinder_Feed';

    /**
     * Magento deployment configuration.
     *
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Magento component registrar.
     *
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * Store configuration helper.
     *
     * @var StoreConfig
     */
    protected $storeConfig;

    /**
     * Product metadata instance.
     *
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Filesystem read factory.
     *
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * Magento scope config interface.
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ModuleData constructor.
     *
     * @param DeploymentConfig $deploymentConfig
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param StoreConfig $storeConfig
     * @param ProductMetadataInterface $productMetadata
     * @param ReadFactory $readFactory
     * @param ScopeConfigInterface $scopeConfig
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
     * Returns an array containing serialized metadata about the Doofinder module.
     *
     * @return mixed[]
     */
    public function get()
    {
        $moduleStruct = new ModuleStruct(
            $this->getModuleVersion(),
            $this->getMagentoVersion(),
            $this->getWebsiteStructures()
        );
        return [$moduleStruct->jsonSerialize()];
    }

    /**
     * Retrieves the module version from the composer.json file.
     *
     * @return string|null
     */
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

    /**
     * Retrieves the Magento edition and version (e.g., Community: 2.4.6).
     *
     * @return string
     */
    private function getMagentoVersion()
    {
        return $this->productMetadata->getEdition() . ': ' . $this->productMetadata->getVersion();
    }

    /**
     * Builds a list of website structures containing store information.
     *
     * @return mixed[]
     */
    private function getWebsiteStructures()
    {
        $websiteStructs = [];
        foreach ($this->storeConfig->getAllWebsites() as $website) {
            $storeStructs = [];
            $websiteId = $website->getId();
            foreach ($this->storeConfig->getWebsiteStores($websiteId) as $store) {
                $storeCode = $store->getCode();
                $storeStructs[] = (new StoreStruct(
                    $store->getId(),
                    $storeCode,
                    $this->storeConfig->getLanguageFromStore($store),
                    $store->getCurrentCurrencyCode(),
                    $this->getInstallationId($store) ?: ""
                ))->jsonSerialize();
            }
            $websiteStructs[] = (new WebsiteStruct(
                $websiteId,
                $website->getName(),
                $website->getCode(),
                $storeStructs
            ))->jsonSerialize();
        }
        return $websiteStructs;
    }

    /**
     * Retrieves the Doofinder installation ID for a given store.
     *
     * @param \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store $store
     * @return string|null
     */
    private function getInstallationId($store)
    {
        $storeGroupId = $store->getStoreGroupId();
        return $this->storeConfig->getValueFromConfig(
            'doofinder_config_config/doofinder_layer/installation_id',
            ScopeInterface::SCOPE_GROUP,
            $storeGroupId
        );
    }
}
