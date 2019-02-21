<?php

namespace Doofinder\Feed\Helper;

/**
 * Basic helper
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
    private $storeManager;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->storeManager = $storeManager;
        $this->moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * Get value as int
     *
     * @param string|integer $value
     * @param mixed $defaultValue
     *
     * @return integer Value as int
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
     * @param string|integer $value
     * @param mixed $defaultValue
     *
     * @return boolean Value as bool
     */
    public function isBoolean($value, $defaultValue)
    {
        if (is_numeric($value)) {
            return ((int)($value *= 1) > 0);
        }

        $yesOptions = ['true', 'on', 'yes'];
        $noOptions  = ['false', 'off', 'no'];

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
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get module setup version.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }

    /**
     * Set content type as xml.
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     * @return void
     */
    public function setXmlHeaders(\Magento\Framework\App\ResponseInterface $response)
    {
        $response->setHeader('Content-type', 'application/xml; charset="utf-8"', true);
    }

    /**
     * Set content type as json.
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     * @return void
     */
    public function setJsonHeaders(\Magento\Framework\App\ResponseInterface $response)
    {
        $response->setHeader('Content-type', 'application/json; charset="utf-8"', true);
    }

    /**
     * Get string param.
     *
     * @param string $paramName
     * @return string|null
     */
    public function getParamString($paramName)
    {
        return is_string($param = $this->_getRequest()->getParam($paramName)) ? $param : null;
    }

    /**
     * Get int param.
     *
     * @param string $paramName
     * @return integer|null
     */
    public function getParamInt($paramName)
    {
        return is_numeric($param = $this->_getRequest()->getParam($paramName)) ? (int) $param : null;
    }

    /**
     * Get boolean param.
     *
     * @param string $paramName
     * @return boolean|null
     */
    public function getParamBoolean($paramName)
    {
        return (($param = $this->_getRequest()->getParam($paramName)) === null) ? null : (boolean) $param;
    }
}
