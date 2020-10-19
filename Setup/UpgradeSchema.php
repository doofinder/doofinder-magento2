<?php

namespace Doofinder\Feed\Setup;

use Doofinder\Feed\Helper\Serializer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\Manager;
use Exception;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Class UpgradeSchema
 * Upgrade database schema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SchemaSetupInterface $setup
     */
    private $setup;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var Serialize
     */
    private $phpSerialize;

    /**
     * UpgradeSchema constructor.
     * @param Serializer $serializer
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     * @param Serialize $phpserialize
     */
    public function __construct(
        Serializer $serializer,
        WriterInterface $configWriter,
        Manager $cacheManager,
        Serialize $phpserialize
    ) {
        $this->serializer = $serializer;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->phpSerialize = $phpserialize;
    }

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

        if (version_compare($context->getVersion(), '0.2.9', '<')) {
            // we perform these operations in UpgradeSchema
            // because of modules sequence.
            // One of the modules can run indexer before our upgrade
            // and setup:upgrade may break down
            $this->removeAttributesFromConfig();
            $this->convertAdditionalAttributes();
            // we have to make sure, that old values are not cached any more
            // because during upgrade, some modules can run indexer
            // and Magento will read old values from cache
            // instead of fixed ones from database
            $this->cacheManager->flush([Config::CACHE_TAG]);
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
            ->newTable($this->setup->getTable(ChangedProduct::TABLE_NAME));

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
        )->addColumn(
            ChangedProduct::FIELD_PRODUCT_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false
            ],
            'ID of a deleted product'
        )->addColumn(
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
            $this->setup->getTable(ChangedProduct::TABLE_NAME),
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
            $this->setup->getTable(ChangedProduct::TABLE_NAME),
            ChangedProduct::FIELD_STORE_CODE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => false,
                'comment' => 'Code of store the change was issued on'
            ]
        );
    }

    /**
     * Remove unused attributes from core_config_data
     * See di.xml instead
     * @return void
     */
    private function removeAttributesFromConfig()
    {
        $connection = $this->setup->getConnection();
        $attributesToRemove = [
            'title',
            'description',
            'categories',
            'link',
            'price',
            'sale_price',
            'availability'
        ];
        $attributesToRemove = array_map(function ($path) {
            return 'doofinder_config_index/feed_attributes/' . $path;
        }, $attributesToRemove);

        $connection->delete($this->setup->getTable('core_config_data'), ['path IN (?)' => $attributesToRemove]);
    }

    /**
     * Convert old Additional Attributes from php serializer to json data
     * @return void
     */
    private function convertAdditionalAttributes()
    {
        $connection = $this->setup->getConnection();
        $path = 'doofinder_config_index/feed_attributes/additional_attributes';
        // phpcs:ignore Ecg.Performance.FetchAll.Found
        $attrs = $connection->fetchAll(
            $connection->select()->from($this->setup->getTable('core_config_data'))->where('path = ?', $path)
        );

        if ($attrs) {
            foreach ($attrs as $attrsPhp) {
                try {
                    $attrsArray = $this->phpSerialize->unserialize($attrsPhp['value']);
                } catch (Exception $exception) {
                    $attrsArray = [];
                }

                foreach ($attrsArray as &$attr) {
                    unset($attr['label']);
                }

                $attrsJson = $this->serializer->serialize($attrsArray);

                // phpcs:ignore Ecg.Performance.Loop.ModelLSD
                $this->configWriter->save(
                    $path,
                    $attrsJson,
                    $attrsPhp['scope'],
                    $attrsPhp['scope_id']
                );
            }
        }
    }
}
