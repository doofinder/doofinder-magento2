<?php

declare(strict_types=1);

namespace Doofinder\Feed\Setup\Patch\Data;

use Exception;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Psr\Log\LoggerInterface;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Serializer\Base64GzJson;

class UpgradeCustomAttributesConfigValuePatch implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $_moduleDataSetup;

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var WriterInterface
     */
    private $_configWriter;
    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        LoggerInterface $logger
    ) {
        $this->_moduleDataSetup = $moduleDataSetup;
        $this->_scopeConfig = $scopeConfig;
        $this->_configWriter = $configWriter;
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
        $customAttributes = $this->_scopeConfig->getValue(StoreConfig::CUSTOM_ATTRIBUTES, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
        if ($customAttributes !== null) {
            $serializer = new Base64GzJson();
            $this->_configWriter->save(
                StoreConfig::CUSTOM_ATTRIBUTES,
                $serializer->serialize($customAttributes),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null
            );
        }
        $this->_moduleDataSetup->endSetup();
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
        return '1.0.7';
    }
}
