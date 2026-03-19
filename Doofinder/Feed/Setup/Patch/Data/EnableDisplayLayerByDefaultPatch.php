<?php

declare(strict_types=1);

namespace Doofinder\Feed\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class EnableDisplayLayerByDefaultPatch implements DataPatchInterface
{
    private const INDICE_CALLBACK_PATH = 'doofinder_config_config/doofinder_layer/indice_callback';

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
     * Remove obsolete indice_callback config entries from core_config_data.
     *
     * The indice_callback mechanism (which gated the Doofinder script behind indexation completion)
     * has been removed. This patch cleans up leftover rows from existing installations.
     */
    public function apply(): EnableDisplayLayerByDefaultPatch
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $tableName = $this->moduleDataSetup->getTable('core_config_data');

        try {
            $deleted = $connection->delete(
                $tableName,
                ['path = ?' => self::INDICE_CALLBACK_PATH]
            );
            $this->logger->info('[Doofinder] Removed ' . $deleted . ' indice_callback rows from core_config_data');
        } catch (\Exception $e) {
            $this->logger->critical('[Doofinder] EnableDisplayLayerByDefaultPatch failed: ' . $e->getMessage());
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

    /**
     * @inheritdoc
     */
    public static function getVersion(): string
    {
        return '1.5.5';
    }
}
