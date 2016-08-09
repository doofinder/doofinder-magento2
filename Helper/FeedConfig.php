<?php

namespace Doofinder\Feed\Helper;

/**
 * Class FeedConfig
 * @package Doofinder\Feed\Helper
 */
class FeedConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array Feed attribute config
     */
    protected $_feedConfig;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * FeedConfig constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Get feed attribute config.
     *
     * @return array
     */
    public function getFeedConfig()
    {
        if (!$this->_feedConfig) {
            $this->_setFeedConfig();
        }

        return $this->_feedConfig;
    }

    /**
     * Set feed config
     */
    protected function _setFeedConfig()
    {
        $this->_feedConfig = [
            'data' => [
                'config' => [
                    'fetchers' => $this->_getFetchers(),
                    'processors' => $this->_getProcessors()
                ]
            ]
        ];
    }

    /**
     * Setup fetchers.
     *
     * @return array
     */
    protected function _getFetchers()
    {
        return [
            'Product' => []
        ];
    }

    /**
     * Setup processors.
     *
     * @return array
     */
    protected function _getProcessors()
    {
        return [
            'Mapper' => $this->_getMapper(),
            'Cleaner' => [],
            'Xml' => []
        ];
    }

    /**
     * Setup feed mapper.
     *
     * @return array
     */
    protected function _getMapper()
    {
        return [
            'map' => $this->_getFeedAttributes()
        ];
    }

    /**
     * Get feed attributes from config.
     *
     * @return array
     */
    protected function _getFeedAttributes()
    {
        $attributes = $this->_scopeConfig->getValue('doofinder_feed_feed/feed_attributes');

        if (array_key_exists('additional_attributes', $attributes)) {
            $additionalKeys = unserialize($attributes['additional_attributes']);
            unset($attributes['additional_attributes']);

            $additionalAttributes = array();
            foreach ($additionalKeys as $key) {
                $additionalAttributes[$key['field']] = $key['additional_attribute'];
            }

            return array_merge($attributes, $additionalAttributes);
        }

        return $attributes;
    }
}