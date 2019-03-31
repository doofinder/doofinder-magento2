<?php

namespace Doofinder\Feed\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;
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

        if (version_compare($context->getVersion(), '0.1.14', '<')) {
            $this->updateProductChangeTraceTable();
        }

        $this->setup->endSetup();
    }

    /**
     * Creates a table for storing identities of changed products.
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
            Table::TYPE_INTEGER,
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
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'ID of a deleted product'
            )
            ->addColumn(
                ChangedProduct::FIELD_OPERATION_TYPE,
                Table::TYPE_TEXT,
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

    /**
     * Updates the table responsible for storing product changes traces.
     *
     * Updates primary key type from INT to BIGINT (future-proof - this table will be populated continously).
     *
     * In case many store views are affected by a single product change, separate rows are created for
     * each of them, holding the same information about product ID and operation.
     *
     * @return void
     */
    private function updateProductChangeTraceTable()
    {
        $connection = $this->setup
            ->getConnection();

        $connection->modifyColumn(
            ChangedProduct::TABLE_NAME,
            ChangedProduct::FIELD_ID,
            [
                'type' => Table::TYPE_BIGINT,
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        );

        $connection->addColumn(
            ChangedProduct::TABLE_NAME,
            ChangedProduct::FIELD_STORE_CODE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => false,
                'comment' => 'Code of store the change was issued on'
            ]
        );
    }
}
