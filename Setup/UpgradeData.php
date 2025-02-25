<?php

namespace Doofinder\Feed\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\App\Helper\Context;

use Doofinder\Feed\Helper\StoreConfig;


class UpgradeData implements UpgradeDataInterface
{
    /** @var StoreConfig */
    private $storeConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param StoreConfig $storeConfig
     * 
     */
    public function __construct(
        Context $context,
        StoreConfig $storeConfig,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->scopeConfig = $context->getScopeConfig();
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.7', '<')) {

            list($scope, $id) = $this->storeConfig->getCurrentScope();
            $customAttributes = $this->scopeConfig->getValue(StoreConfig::CUSTOM_ATTRIBUTES, $scope, $id);
            $jsonDecodedAttributes = json_decode($customAttributes, true) ?: [];

            $encodedAttributes = base64_encode(gzcompress(json_encode($jsonDecodedAttributes)));
            $this->storeConfig->setCustomAttributes($encodedAttributes);
        }
    }
}
