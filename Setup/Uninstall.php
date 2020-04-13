<?php

namespace Doofinder\Feed\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct;

/**
 * Class Uninstall
 * Uninstall the module
 */
class Uninstall implements UninstallInterface
{
    /**
     * Uninstall
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // phpcs:enable
        $setup->startSetup();

        $setup->getConnection()->dropTable(
            $setup->getTable(ChangedProduct::TABLE_NAME)
        );

        $setup->endSetup();
    }
}
