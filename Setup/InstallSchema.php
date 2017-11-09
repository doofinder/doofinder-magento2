<?php

namespace Doofinder\Feed\Setup;

use \Magento\Framework\Setup\InstallSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;
use \Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    const CRON_TABLE_NAME = 'doofinder_feed_cron';
    const LOG_TABLE_NAME = 'doofinder_feed_log';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
    // @codingStandardsIgnoreEnd
        $setup->startSetup();

        $this->setupCronTable($setup);
        $this->setupLogTable($setup);

        $setup->endSetup();
    }

    private function setupCronTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable(self::CRON_TABLE_NAME)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Entity ID'
            )
            ->addColumn(
                'store_code',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Store Code'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Status'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Message'
            )
            ->addColumn(
                'error_stack',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Error Stack'
            )
            ->addColumn(
                'complete',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Complete'
            )
            ->addColumn(
                'next_run',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Next run'
            )
            ->addColumn(
                'next_iteration',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Next iteration'
            )
            ->addColumn(
                'last_feed_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Last feed name'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )
            ->addColumn(
                'offset',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Offset'
            )
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');

        $setup->getConnection()->createTable($table);
    }

    private function setupLogTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable(self::LOG_TABLE_NAME)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                ],
                'Entity ID'
            )
            ->addColumn(
                'process_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'  => true, 'nullable'  => false],
                'Doofinder Feed Process ID'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['nullable'  => false],
                'Type'
            )
            ->addColumn(
                'time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable'  => false, 'default' => Table::TIMESTAMP_INIT],
                'Time'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                null,
                ['nullable'  => false],
                'Message'
            );

        // Add indexes to log table
        $table->addIndex(
            $setup->getIdxName(
                self::LOG_TABLE_NAME,
                ['process_id', 'type'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['process_id', 'type'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        );
        $table->addIndex(
            $setup->getIdxName(
                self::LOG_TABLE_NAME,
                ['time'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['time'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        );

        // Add foreign keys
        $table->addForeignKey(
            $setup->getFkName(
                self::LOG_TABLE_NAME,
                'process_id',
                self::CRON_TABLE_NAME,
                'entity_id'
            ),
            'process_id',
            $setup->getTable(self::CRON_TABLE_NAME),
            'entity_id',
            Table::ACTION_CASCADE,
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }
}
