<?php

namespace Magento\Doofinder\Feed\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Vendor\Module\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Store\Model\ScopeInterface;

class UpgradeToStoreGroupPatch implements DataPatchInterface, PatchVersionInterface
{
    protected $configWriter;
    protected $scopeConfig;
    protected $groupCollectionFactory;

    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        GroupCollectionFactory $groupCollectionFactory
    )
    {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function apply()
    {
        $groupCollection = $this->groupCollectionFactory->create();
        $scriptIdPath = 'doofinder_config_config/doofinder_layer/script';
        $installationIdPath = 'doofinder_config_config/doofinder_layer/installation_id';

        foreach ($groupCollection as $group) {
            $websiteId = $group->getWebsiteId();
            $script = $this->scopeConfig->getValue($scriptIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
            if(!empty($script)){
                $this->configWriter->save($scriptIdPath, $script, ScopeInterface::SCOPE_GROUP, $group->getId());
                $this->configWriter->delete($scriptIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
            }

            $installationId = $this->scopeConfig->getValue($installationIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
            if(!empty($installationId)){
                $this->configWriter->save($installationIdPath, $installationId, ScopeInterface::SCOPE_GROUP, $group->getId());
                $this->configWriter->delete($installationIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
            }
        }
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
        return '0.10.7';
    }
}