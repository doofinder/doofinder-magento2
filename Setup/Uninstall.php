<?php

namespace Doofinder\Feed\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
    // @codingStandardsIgnoreEnd
        $setup->startSetup();

        $setup->getConnection()->dropTable(
            $setup->getTable(InstallSchema::CRON_TABLE_NAME)
        );
        $setup->getConnection()->dropTable(
            $setup->getTable(InstallSchema::LOG_TABLE_NAME)
        );

        $setup->endSetup();
    }
}
