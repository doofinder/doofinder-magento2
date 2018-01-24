<?php

namespace Doofinder\Feed\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Uninstall
 */
class Uninstall implements UninstallInterface
{
    /**
     * Uninstall
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
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
