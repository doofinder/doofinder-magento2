<?php

declare(strict_types=1);

namespace Doofinder\Feed\Setup\Patch\Data;

use Exception;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

use Doofinder\Feed\Helper\StoreConfig;


class UpgradeCustomAttributesConfigValuePatch implements DataPatchInterface
{
    /** 
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ModuleContextInterface
     */
    private $moduleContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param StoreConfig $storeConfig
     */
    public function __construct(
        Context $context,
        StoreConfig $storeConfig,
        ModuleDataSetupInterface $moduleDataSetup,
        ModuleContextInterface $moduleContext,
        LoggerInterface $logger
    ) {
        $this->storeConfig = $storeConfig;
        $this->scopeConfig = $context->getScopeConfig();
        $this->moduleDataSetup = $moduleDataSetup;
        $this->moduleContext = $moduleContext;
        $this->logger = $logger;
    }

    public function apply()
    {
        if (version_compare($this->moduleContext->getVersion(), '1.0.7', '<')) {
            $this->moduleDataSetup->startSetup();
            try {
                list($scope, $id) = $this->storeConfig->getCurrentScope();
                $customAttributes = $this->scopeConfig->getValue(StoreConfig::CUSTOM_ATTRIBUTES, $scope, $id);
                if ($customAttributes !== null) {
                    $jsonDecodedAttributes = json_decode($customAttributes, true) ?: [];
                    $jsonData = json_encode($jsonDecodedAttributes);
                    if ($jsonData === false) {
                        throw new \Exception('Failed to encode attributes to JSON');
                    }
                    $compressedData = gzcompress($jsonData);
                    if ($compressedData === false) {
                        throw new \Exception('Failed to compress attributes data');
                    }
                    $encodedAttributes = base64_encode($compressedData);
                    $this->storeConfig->setCustomAttributes($encodedAttributes);
                }
            } catch (Exception $e) {
                $this->logger->error('Failed to update custom attributes: ' . $e->getMessage());
            } finally {
                $this->moduleDataSetup->endSetup();
            }
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
