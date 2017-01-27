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

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable(self::CRON_TABLE_NAME)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'ID'
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

        $installer->getConnection()->createTable($table);

        // Add log table
        $table = $installer->getConnection()
            ->newTable(self::LOG_TABLE_NAME)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                ],
                'ID'
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
            $installer->getIdxName(
                self::LOG_TABLE_NAME,
                ['process_id', 'type'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['process_id', 'type'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        );
        $table->addIndex(
            $installer->getIdxName(
                self::LOG_TABLE_NAME,
                ['time'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['time'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        );

        // Add foreign keys
        $table->addForeignKey(
            $installer->getFkName(
                self::LOG_TABLE_NAME,
                'process_id',
                self::CRON_TABLE_NAME,
                'id'
            ),
            'process_id',
            $installer->getTable(self::CRON_TABLE_NAME),
            'id',
            Table::ACTION_CASCADE,
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
