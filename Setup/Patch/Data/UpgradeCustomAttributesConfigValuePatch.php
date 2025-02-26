<?php

declare(strict_types=1);

namespace Doofinder\Feed\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Serializer\Base64GzJson;
use Exception;

class UpgradeCustomAttributesConfigValuePatch implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $_moduleDataSetup;

    /**
     * @var ResourceConnection
     */
    private $_resourceConnection;

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var WriterInterface
     */
    private $_configWriter;

    /**
     * @var Base64GzJson
     */
    private $_serializer;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param Base64GzJson $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        Base64GzJson $serializer,
        LoggerInterface $logger
    ) {
        $this->_moduleDataSetup = $moduleDataSetup;
        $this->_resourceConnection = $resourceConnection;
        $this->_scopeConfig = $scopeConfig;
        $this->_configWriter = $configWriter;
        $this->_serializer = $serializer;
        $this->_logger = $logger;
    }

    /**
     * Apply data patch to upgrade custom attributes storage format.
     *
     * This patch compresses and base64 encodes custom attributes when updating
     * from a module version less than 1.0.7. This change reduces storage size.
     *
     * @return $this
     */
    public function apply(): UpgradeCustomAttributesConfigValuePatch
    {
        $this->_moduleDataSetup->startSetup();
        $this->_logger->info('Starting custom attributes upgrade process');

        $connection = $this->_resourceConnection->getConnection();

        try {
            // Start transaction
            $connection->beginTransaction();
            $tableName = $this->_resourceConnection->getTableName('core_config_data');

            $select = $connection->select()
                ->from(['c' => $tableName], ['scope', 'scope_id'])
                ->where('c.path = ?', StoreConfig::CUSTOM_ATTRIBUTES);

            $configuredScopes = $connection->fetchAll($select);
            $this->_logger->info('Found ' . count($configuredScopes) . ' scopes with custom attributes configuration');
            foreach ($configuredScopes as $scope) {
                $customAttributes = $this->_scopeConfig->getValue(StoreConfig::CUSTOM_ATTRIBUTES, $scope['scope'], $scope['scope_id']);
                $this->_configWriter->save(
                    StoreConfig::CUSTOM_ATTRIBUTES,
                    $this->_serializer->serialize($customAttributes),
                    $scope['scope'],
                    $scope['scope_id']
                );
                $this->_logger->info('Successfully upgraded custom attributes for scope: ' . $scope['scope'] . ', scope_id: ' . $scope['scope_id']);
            }
            $connection->commit();
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $connection->rollBack();
            $this->_logger->critical('Failed to upgrade custom attributes: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
        $this->_moduleDataSetup->endSetup();
        $this->_logger->info('Completed custom attributes upgrade process');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion(): string
    {
        return '1.0.8';
    }
}
