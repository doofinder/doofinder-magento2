<?php
namespace Doofinder\Feed\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Store\Model\ScopeInterface;

class UpgradeData implements UpgradeDataInterface
{


    protected $configWriter;
    protected $scopeConfig;
    protected $groupCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        GroupCollectionFactory $groupCollectionFactory
    )
    {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if(version_compare($context->getVersion(), '0.0.1', '>')) {
            if (version_compare($context->getVersion(), '0.10. 7', '<')) {
                $groupCollection = $this->groupCollectionFactory->create();
                $scriptIdPath = 'doofinder_config_config/doofinder_layer/script';
                $installationIdPath = 'doofinder_config_config/doofinder_layer/installation_id';
    
                foreach ($groupCollection as $group) {
                    $websiteId = $group->getWebsiteId();
                    $script = $this->scopeConfig->getValue($scriptIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
                    if(isset($script)){
                        $this->configWriter->save($scriptIdPath, $script, ScopeInterface::SCOPE_GROUP, $group->getId());
                        $this->configWriter->delete($scriptIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
                    }
    
                    $installationId = $this->scopeConfig->getValue($installationIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
                    if(isset($installationId)){
                        $this->configWriter->save($installationIdPath, $installationId, ScopeInterface::SCOPE_GROUP, $group->getId());
                        $this->configWriter->delete($installationIdPath, ScopeInterface::SCOPE_WEBSITES, $websiteId);
                    }
                }
            }
            $setup->endSetup();
        }
    }
        
}