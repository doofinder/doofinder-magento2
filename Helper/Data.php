<?php

namespace Doofinder\Feed\Helper;

/**
 * Class Data
 * @package Doofinder\Feed\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Module name
     */
    const MODULE_NAME = "Doofinder_Feed";

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_storeManager = $storeManager;
        $this->_moduleList = $moduleList;
        $this->_logger = $logger;
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
     * Get magento base url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get module setup version.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }
}
