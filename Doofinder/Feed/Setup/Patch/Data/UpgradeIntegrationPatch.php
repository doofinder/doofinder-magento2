<?php

namespace Doofinder\Feed\Setup\Patch\Data;

use Exception;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpgradeIntegrationPatch
 *
 * Adds or updates permissions for the "Doofinder Integration" after a module upgrade.
 */
class UpgradeIntegrationPatch implements DataPatchInterface
{
    private const DOOFINDER_INTEGRATION_NAME = 'Doofinder Integration';

    /**
     * List of required ACL resources for the integration.
     *
     * @var string[]
     */
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

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpgradeIntegrationPatch constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param IntegrationServiceInterface $integrationServiceInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        IntegrationServiceInterface $integrationServiceInterface,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->integrationService = $integrationServiceInterface;
        $this->logger = $logger;
    }

    /**
     * Applies ACL resource updates to the Doofinder Integration.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        try {
            $integration = $this->integrationService->findByName(self::DOOFINDER_INTEGRATION_NAME);
            $integrationData = [
                'integration_id' => $integration->getId(),
                'name' => $integration->getName(),
                'resource' => $this->resources
            ];
            $this->integrationService->update($integrationData);
        } catch (Exception $e) {
            $this->logger->error(sprintf(
                'Error updating Doofinder integration: %s in %s on line %d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        } finally {
            $this->moduleDataSetup->endSetup();
        }
    }

    /**
     * Returns the list of dependencies for this data patch.
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Returns the list of aliases for this data patch.
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
