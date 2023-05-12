<?php
namespace Doofinder\Feed\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.10.7', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['scope' => 'group'], 
                ['path IN (?)' => ['doofinder_config_config/doofinder_layer/installation_id', 'doofinder_config_config/doofinder_layer/script']] 
            );
        }

        $setup->endSetup();
    }
}