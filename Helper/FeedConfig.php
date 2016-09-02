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
     * @var array Config for given store code
     */
    protected $_config;

    /**
     * @var array Config parameters
     */
    protected $_params;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * FeedConfig constructor.
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig
    ) {
        $this->_storeConfig = $storeConfig;
    }

    /**
     * Get feed attribute config.
     *
     * @param string $storeCode
     * @param array $params = []
     * @return array
     */
    public function getFeedConfig($storeCode = null, array $params = [])
    {
        if (!isset($this->_feedConfig[$storeCode])) {
            $this->_params = $params;
            $this->setFeedConfig($storeCode);
        }

        return $this->_feedConfig[$storeCode];
    }

    /**
     * Set feed config
     *
     * @param string $storeCode
     */
    protected function setFeedConfig($storeCode)
    {
        $this->_config = $this->_storeConfig->getStoreConfig($storeCode);

        $this->_feedConfig[$storeCode] = [
            'data' => [
                'config' => [
                    'fetchers' => $this->getFetchers(),
                    'processors' => $this->getProcessors()
                ]
            ]
        ];
    }

    /**
     * Setup fetchers.
     *
     * @return array
     */
    protected function getFetchers()
    {
        return [
            'Product' => [
                'offset' => $this->getParam('offset'),
                'limit' => $this->getParam('limit'),
            ],
        ];
    }

    /**
     * Setup processors.
     *
     * @return array
     */
    protected function getProcessors()
    {
        return [
            'Mapper\Product' => $this->getMapper(),
            'Cleaner' => [],
            'Xml' => []
        ];
    }

    /**
     * Setup feed mapper.
     *
     * @return array
     */
    protected function getMapper()
    {
        return [
            'minimal_price' => $this->getParam('minimal_price'),
            'image_size' => $this->_config['image_size'],
            'split_configurable_products' => $this->_config['split_configurable_products'],
            'export_product_prices' => $this->_config['export_product_prices'],
            'map' => $this->getFeedAttributes()
        ];
    }

    /**
     * Get feed attributes from config.
     *
     * @return array
     */
    protected function getFeedAttributes()
    {
        $attributes = $this->_config['attributes'];

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

    /**
     * Get param
     *
     * @param string $key
     * @return mixed
     */
    protected function getParam($key)
    {
        return isset($this->_params[$key]) ? $this->_params[$key] : null;
    }
}
