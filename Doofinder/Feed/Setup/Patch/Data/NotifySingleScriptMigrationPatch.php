<?php

declare(strict_types=1);

namespace Doofinder\Feed\Setup\Patch\Data;

use Doofinder\Feed\ApiClient\Client;
use Doofinder\Feed\ApiClient\ClientFactory;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Patch to notify dooplugins API about migration to single script.
 * This patch runs once when upgrading to the version that introduces single script.
 */
class NotifySingleScriptMigrationPatch implements DataPatchInterface
{
    private const ENDPOINT_MIGRATE_SINGLE_SCRIPT = '/magento/migrate-unique-script';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var GroupCollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NotifySingleScriptMigrationPatch constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ClientFactory $clientFactory
     * @param StoreConfig $storeConfig
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ClientFactory $clientFactory,
        StoreConfig $storeConfig,
        GroupCollectionFactory $groupCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->clientFactory = $clientFactory;
        $this->storeConfig = $storeConfig;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Applies the patch: notifies dooplugins API about single script migration for all store groups.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $groupCollection = $this->groupCollectionFactory->create();

        foreach ($groupCollection as $group) {
            $storeGroupId = (int)$group->getId();
            
            // Get installation ID for the store group using the helper method
            $installationId = $this->storeConfig->getInstallationId($storeGroupId);

            if ($installationId === null) {
                continue;
            }

            try {
                // Create dooplugins client
                $client = $this->clientFactory->create([
                    'apiType' => Client::DOOPLUGINS
                ]);

                // Notify dooplugins about single script migration
                $body = [
                    'installation_id' => $installationId,
                ];

                $client->post(self::ENDPOINT_MIGRATE_SINGLE_SCRIPT, $body);
                
                $this->logger->info(
                    'Successfully notified dooplugins about single script migration',
                    [
                        'store_group_id' => $storeGroupId,
                        'installation_id' => $installationId
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->error(
                    'Failed to notify dooplugins about single script migration',
                    [
                        'store_group_id' => $storeGroupId,
                        'installation_id' => $installationId,
                        'error' => $e->getMessage()
                    ]
                );
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
