<?php

namespace Doofinder\Feed\Setup\Patch\Data;

use Exception;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Integration\Api\IntegrationServiceInterface;

class UpgradeIntegrationPatch implements DataPatchInterface
{
    private const DOOFINDER_INTEGRATION_NAME = 'Doofinder Integration';

    private $resources = [
        'Magento_Catalog::catalog',
        'Magento_Catalog::catalog_inventory',
        'Magento_Catalog::products',
        'Magento_Catalog::categories',
        'Magento_Backend::stores',
        'Magento_Backend::stores_settings',
        'Magento_Backend::store',
        'Magento_CatalogInventory::cataloginventory',
        'Magento_Backend::stores_attributes',
        'Magento_Catalog::attributes_attributes',
        'Magento_Catalog::sets',
        'Magento_Cms::page'
    ];

    private $integrationService;
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        IntegrationServiceInterface $integrationServiceInterface
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->integrationService = $integrationServiceInterface;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        try {
            $integration = $this->integrationService->findByName(self::DOOFINDER_INTEGRATION_NAME);
            $integrationData = ['integration_id' => $integration->getId(), 'name' => $integration->getName(), 'resource' => $this->resources];
            $this->integrationService->update($integrationData);
        } catch(Exception $e) {
        } finally {
            $this->moduleDataSetup->endSetup();
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}