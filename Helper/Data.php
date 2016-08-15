<?php

namespace Doofinder\Feed\Helper;

/**
 * Class Data
 * @package Doofinder\Feed\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MODULE_NAME = "Doofinder_Feed";

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var array Store config for feed.
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;


    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_moduleList = $moduleList;
        $this->_logger = $logger;
    }

    /**
     * Return array with store config.
     *
     * @return array
     */
    public function getStoreConfig()
    {
        if (!$this->_storeConfig) {
            $this->_setStoreConfig();
        }

        return $this->_storeConfig;
    }

    /**
     * Set store config.
     *
     * @return \Doofinder\Feed\Helper\Data;
     */
    protected function _setStoreConfig()
    {
        $scopeStore = $this->_getScopeStore();
        $storeCode = $this->_getStoreCode();

        $this->_storeConfig = array(
            'grouped'
                => $this->_scopeConfig->getValue('doofinder_feed_feed/feed_settings/grouped', $scopeStore),
            'image_size'
                => $this->_scopeConfig->getValue('doofinder_feed_feed/feed_settings/image_size', $scopeStore),
            'store_code'
                => $storeCode,
        );

        return $this;
    }

    /**
     * Get new xml file name.
     *
     * @param string $name XML file name
     * @param string $code Store code
     *
     * @return string New xml file name
     */
    protected function _processXmlName($name = 'doofinder-{store_code}.xml', $code = 'default')
    {
        $pattern = '/\{\s*store_code\s*\}/';

        $newName = preg_replace($pattern, $code, $name);
        return $newName;
    }

    /**
     * Get value as int
     *
     * @param string|int $value Value
     * @param mixed $defaultValue Default value
     *
     * @return int Value as int
     */
    public function getInteger($value, $defaultValue)
    {
        if (is_numeric($value)) {
            return (int)($value *= 1);
        }

        return $defaultValue;
    }

    /**
     * Get value as boolean
     *
     * @param string|int $value Value
     * @param mixed $defaultValue Default value
     *
     * @return bool Value as bool
     */
    public function isBoolean($value, $defaultValue)
    {
        if (is_numeric($value)) {
            return ((int)($value *= 1) > 0);
        }

        $yesOptions = array('true', 'on', 'yes');
        $noOptions  = array('false', 'off', 'no');

        if (in_array($value, $yesOptions)) {
            return true;
        }

        if (in_array($value, $noOptions)) {
            return false;
        }

        return $defaultValue;
    }

    /**
     * Get store code
     *
     * @return string Store code
     */
    protected function _getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * get Scope store
     *
     * @return string Scope store
     */
    protected function _getScopeStore()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getModuleVersion()
    {
        return $this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }

    /**
     * Set store view for generator.
     *
     * @param int $storeID
     */
    public function setCurrentStore($storeID)
    {
        try {
            if ($this->_storeManager->getStore($storeID)) {
                $this->_storeManager->setCurrentStore($storeID);
            }
        } catch (\Exception $e) {
            $this->_logger->error('Store ID: '.$storeID.' - '.$e->getMessage());
        }
    }
}
