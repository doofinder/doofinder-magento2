<?php

namespace Doofinder\Feed\Helper;

/**
 * Class StoreConfig
 *
 * @package Doofinder\Feed\Helper
 */
class StoreConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Path to cron config in config.xml/core_config_data
     */
    const FEED_CRON_CONFIG = 'doofinder_feed_feed/feed_cron';

    /**
     * Path to feed settings in config.xml/core_config_data
     */
    const FEED_SETTINGS_CONFIG = 'doofinder_feed_feed/feed_settings';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * StoreConfig constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
    }

    /**
     * Return array with store config.
     *
     * @return array
     */
    public function getStoreConfig()
    {
        $scopeStore = $this->getScopeStore();
        $storeCode['store_code'] = $this->getStoreCode();

        $config = array_merge(
            $storeCode,
            $this->_scopeConfig->getValue(self::FEED_CRON_CONFIG, $scopeStore),
            $this->_scopeConfig->getValue(self::FEED_SETTINGS_CONFIG, $scopeStore)
        );

        $config['start_time'] = explode(',', $config['start_time']);

        return $config;
    }

    /**
     * Get store code.
     *
     * @return string Store code
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * Get Scope store.
     *
     * @return string Scope store
     */
    public function getScopeStore()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
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
