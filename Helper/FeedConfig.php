<?php

namespace Doofinder\Feed\Helper;

/**
 * Feed config helper
 */
class FeedConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array Feed attribute config
     */
    private $feedConfig;

    /**
     * @var array Config for given store code
     */
    private $config;

    /**
     * @var array Config parameters
     */
    private $params;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * FeedConfig constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig
    ) {
        $this->storeConfig = $storeConfig;
        parent::__construct($context);
    }

    /**
     * Get feed minimal config.
     *
     * @param string $storeCode
     * @return array
     */
    public function getLeanFeedConfig($storeCode = null)
    {
        $this->config = $this->storeConfig->getStoreConfig($storeCode);

        return [
            'data' => [
                'config' => [
                    'fetchers' => [],
                    'processors' => $this->getProcessors(),
                ],
            ],
        ];
    }

    /**
     * Get feed attribute config.
     *
     * @param string $storeCode
     * @param array $params
     * @return array
     */
    public function getFeedConfig($storeCode = null, array $params = [])
    {
        if (!isset($this->feedConfig[$storeCode])) {
            $this->params = $params;
            $this->setFeedConfig($storeCode);
        }

        return $this->feedConfig[$storeCode];
    }

    /**
     * Set feed config
     *
     * @param string $storeCode
     * @return void
     */
    private function setFeedConfig($storeCode)
    {
        $config = $this->getLeanFeedConfig($storeCode);

        // Add basic product fetcher
        $config['data']['config']['fetchers'] = $this->getFetchers();

        // Add basic xml processor
        $config['data']['config']['processors']['Xml'] = [];

        $this->feedConfig[$storeCode] = $config;
    }

    /**
     * Setup fetchers.
     *
     * @return array
     */
    private function getFetchers()
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
    private function getProcessors()
    {
        return [
            'Mapper' => $this->getMapper(),
            'Filter' => [],
            'Cleaner' => [],
        ];
    }

    /**
     * Setup feed mapper.
     *
     * @return array
     */
    private function getMapper()
    {
        return [
            'image_size' => $this->config['image_size'],
            'split_configurable_products' => $this->config['split_configurable_products'],
            'export_product_prices' => $this->config['export_product_prices'],
            'price_tax_mode' => $this->config['price_tax_mode'],
            'categories_in_navigation' => $this->config['categories_in_navigation'],
            'map' => $this->getFeedAttributes()
        ];
    }

    /**
     * Get feed attributes from config.
     *
     * @return array
     */
    private function getFeedAttributes()
    {
        return $this->config['attributes'];
    }

    /**
     * Get param
     *
     * @param string $key
     * @return mixed
     */
    private function getParam($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * Get feed password
     *
     * @param string $storeCode
     * @return mixed
     */
    public function getFeedPassword($storeCode = null)
    {
        $storeConfig = $this->storeConfig->getStoreConfig($storeCode);
        return isset($storeConfig['password']) ? $storeConfig['password'] : null;
    }
}
