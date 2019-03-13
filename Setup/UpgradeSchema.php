<?php

namespace Doofinder\Feed\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct;

// phpcs:disable MEQP2.SQL.MissedIndexes.MissedIndexes
// phpcs:disable PSR2.Methods.FunctionCallSignature.Indent

/**
 * Upgrades database schema.
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SchemaSetupInterface $setup
     */
    private $setup;

    /**
     * Performs database schema upgrade.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $this->setup = $setup;
        $this->setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.13', '<')) {
            $this->setupProductChangeTraceTable();
        }

        $this->setup->endSetup();
    }

    /**
     * Creates a table for storing identities of deleted products.
     *
     * @return void
     */
    private function setupProductChangeTraceTable()
    {
        $table = $this->setup
            ->getConnection()
            ->newTable(ChangedProduct::TABLE_NAME);

        // phpcs:disable Indent
        $table->addColumn(
            ChangedProduct::FIELD_ID,
            $table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Row ID'
        )
            ->addColumn(
                ChangedProduct::FIELD_PRODUCT_ID,
                $table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'ID of a deleted product'
            )
            ->addColumn(
                ChangedProduct::FIELD_OPERATION_TYPE,
                $table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Operation type'
            );

        $this->setup
            ->getConnection()
            ->createTable($table);
    }
}
