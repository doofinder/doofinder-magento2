<?php

namespace Doofinder\Feed\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UpgradeToStoreGroupPatch implements DataPatchInterface, PatchVersionInterface
{
    private $moduleDataSetup;
    private $storeManager;
    private $configWriter;
    private $scopeConfig;
    private $logger;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StoreManagerInterface $storeManager,
        WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    ) {
        $this->logger->debug("Entra en el construct");
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeManager = $storeManager;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website) {
            $groups = $website->getGroups();
            
            foreach ($groups as $group) {
                $configPathScript = 'doofinder_config_config/doofinder_layer/script';
                $configPathInstallationId = 'doofinder_config_config/doofinder_layer/installation_id';
                
                $valueScript = $this->scopeConfig->getValue($configPathScript, ScopeInterface::SCOPE_WEBSITES, $website->getId());
                $valueInstallationId = $this->scopeConfig->getValue($configPathInstallationId, ScopeInterface::SCOPE_WEBSITES, $website->getId());
                
                $this->configWriter->save($configPathScript, $valueScript, ScopeInterface::SCOPE_GROUPS, $group->getId());
                $this->configWriter->save($configPathInstallationId, $valueInstallationId, ScopeInterface::SCOPE_GROUPS, $group->getId());
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public static function getVersion()
    {
        return '0.11.0';
    }
}