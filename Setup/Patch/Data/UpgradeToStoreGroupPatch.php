<?php

namespace Doofinder\Feed\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UpgradeToStoreGroupPatch implements DataPatchInterface
{
    private $moduleDataSetup;
    private $configWriter;
    private $scopeConfig;
    private $groupCollectionFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        GroupCollectionFactory $groupCollectionFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $groupCollection = $this->groupCollectionFactory->create();
        $scriptIdPath = 'doofinder_config_config/doofinder_layer/script';
        $installationIdPath = 'doofinder_config_config/doofinder_layer/installation_id';

        foreach ($groupCollection as $group) {
            $websiteId = $group->getWebsiteId();
            $script = $this->scopeConfig->getValue($scriptIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
            if (!empty($script)) {
                $this->configWriter->save($scriptIdPath, $script, ScopeInterface::SCOPE_GROUP, $group->getId());
                $this->configWriter->delete($scriptIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
            }

            $installationId = $this->scopeConfig->getValue($installationIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
            if (!empty($installationId)) {
                $this->configWriter->save($installationIdPath, $installationId, ScopeInterface::SCOPE_GROUP, $group->getId());
                $this->configWriter->delete($installationIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
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
}