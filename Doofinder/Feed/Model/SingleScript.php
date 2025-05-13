<?php

namespace Doofinder\Feed\Model;

use Doofinder\Feed\Helper\Constants;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\SingleScriptStruct;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SingleScript
 *
 * Handles the generation and management of the Doofinder script tags
 * across different store configurations, including cache clearing.
 */
class SingleScript
{
    /**
     * @var DeploymentConfig
     * Configuration for deployment settings.
     */
    protected $deploymentConfig;

    /**
     * @var ComponentRegistrarInterface
     * Component registrar to manage component registration.
     */
    protected $componentRegistrar;

    /**
     * @var StoreConfig
     * Helper for handling store configuration.
     */
    protected $storeConfig;

    /**
     * @var ReadFactory
     * Factory for reading filesystem directories.
     */
    protected $readFactory;

    /**
     * @var ScopeConfigInterface
     * Interface for managing scope-based configuration values.
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     * Logger interface for logging errors or information.
     */
    private $logger;

    /**
     * SingleScript constructor.
     *
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
     * Replaces the existing script tag with the updated Doofinder script tag.
     *
     * @return string[] Returns an array containing the updated script tag as a JSON-encoded string.
     */
    public function replace()
    {
        $singleScriptStruct = new SingleScriptStruct(
            $this->replaceAndGetScripts()
        );
        return [$singleScriptStruct->jsonSerialize()];
    }

    /**
     * Replaces and generates script tags for all websites and stores.
     *
     * @return string[] List of script tags generated for each store.
     */
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

                $singleScript = '<script src="' . sprintf(
                    Constants::DOOFINDER_SCRIPT_URL_FORMAT,
                    $region,
                    $installationId
                ) . '" async></script>';

                $storeGroupId = $store->getStoreGroupId();
                $scripts[] = trim($singleScript);
                $this->storeConfig->setDisplayLayer($singleScript, $storeGroupId);
            }
        }

        $this->executeCacheCleaner();

        return $scripts;
    }

    /**
     * Retrieves the installation ID for a given store.
     *
     * @param StoreInterface $store The store to get the installation ID for.
     * @return string|null The installation ID or null if not found.
     */
    private function getInstallationId(StoreInterface $store): ?string
    {
        $storeGroupId = $store->getStoreGroupId();
        return $this->storeConfig->getValueFromConfig(
            'doofinder_config_config/doofinder_layer/installation_id',
            ScopeInterface::SCOPE_GROUP,
            $storeGroupId
        );
    }

    /**
     * Extracts the region from the API key.
     *
     * @return string The region or an empty string if not valid.
     */
    private function getRegionFromApiKey(): string
    {
        $apiKey = $this->storeConfig->getValueFromConfig('doofinder_config_config/doofinder_account/api_key');
        if (empty($apiKey)) {
            return '';
        }
        $apiKeyParts = explode('-', $apiKey);
        $region = $apiKeyParts[0];

        if (0 === preg_match('/^(us|eu)[0-9]+$/', $region)) {
            return '';
        }

        return $region;
    }

    /**
     * Cleans the cache of specific types, such as 'full_page'.
     *
     * Possible types:
     * 'config','layout','block_html','collections','reflection',
     * 'db_ddl','eav','config_integration','config_integration_api',
     * 'full_page','translate','config_webservice'
     *
     * @throws \Exception If cache cleaning fails.
     */
    private function executeCacheCleaner(): void
    {
        try {
            $objectManager = ObjectManager::getInstance();
            $cacheTypeList = $objectManager->create(TypeListInterface::class);
            $type = 'full_page';
            $cacheTypeList->cleanType($type);
        } catch (\Exception $e) {
            $this->logger->error("Error cleaning cache: " . $e->getMessage());
        }
    }
}
