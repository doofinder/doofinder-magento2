<?php

declare(strict_types=1);

namespace Doofinder\Feed\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class RemoveCustomAttributesConfigPatch implements DataPatchInterface
{
    private const CUSTOM_ATTRIBUTES_PATH_PREFIX = 'doofinder_config_config/doofinder_custom_attributes';

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    /**
     * Remove obsolete custom attributes config entries from core_config_data.
     *
     * The custom attributes selection has been removed: every indexable attribute is now sent to
     * Doofinder and the fields to index are chosen from the Doofinder admin panel. This patch cleans
     * up the leftover rows from existing installations.
     */
    public function apply(): RemoveCustomAttributesConfigPatch
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $tableName = $this->moduleDataSetup->getTable('core_config_data');

        try {
            $deleted = $connection->delete(
                $tableName,
                ['path LIKE ?' => self::CUSTOM_ATTRIBUTES_PATH_PREFIX . '%']
            );
            $this->logger->info(
                '[Doofinder] Removed ' . $deleted . ' custom attributes rows from core_config_data'
            );
        } catch (\Exception $e) {
            $this->logger->critical('[Doofinder] RemoveCustomAttributesConfigPatch failed: ' . $e->getMessage());
            throw $e;
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
