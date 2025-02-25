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
            $custom_attributes = $this->scopeConfig->getValue(StoreConfig::CUSTOM_ATTRIBUTES, $scope, $id);
            $json_decoded_attributes = json_decode($custom_attributes, true);
            if (null !== $json_decoded_attributes) {
                $encoded_attributes = base64_encode(gzcompress(json_encode($json_decoded_attributes)));
                $this->storeConfig->setCustomAttributes($encoded_attributes);
            }
        }
    }
}
