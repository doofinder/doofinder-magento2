<?php

namespace Doofinder\Feed\Controller\Feed;

/**
 * Class Config
 *
 * @package Doofinder\Feed\Controller\Feed
 */
class Config extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $_productMetadata;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $_helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $_filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Doofinder\Feed\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Doofinder\Feed\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_productMetadata = $productMetadata;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_storeConfig = $storeConfig;
        $this->_schedule = $schedule;
        $this->_filesystem = $filesystem;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Execute.
     */
    public function execute()
    {
        $this->_helper->setJsonHeaders($this->getResponse());

        $config = [
            'platform' => [
                'name' => 'Magento',
                'edition' => $this->_productMetadata->getEdition(),
                'version' => $this->_productMetadata->getVersion(),
            ],
            'module' => [
                'version' => $this->_helper->getModuleVersion(),
                'feed' => $this->_storeManager->getStore()->getUrl('doofinder/feed'),
                'options' => [
                    'language' => [],
                ],
                'configuration' => [],
            ],
        ];

        foreach ($this->_storeManager->getStores() as $store) {
            $storeCode = $store->getCode();
            $settings = $this->_storeConfig->getStoreConfig($storeCode);

            if ($settings['enabled']) {
                $feedUrl = $this->_schedule->getFeedFileUrl($storeCode, false);
                $feedExists = $this->_schedule->isFeedFileExist($storeCode);
            } else {
                $feedUrl = $store->getUrl('doofinder/feed');
                $feedExists = true;
            }

            $config['module']['options']['language'][] = $storeCode;
            $config['module']['configuration'][$storeCode] = [
                'language' => strtoupper(substr($this->_scopeConfig->getValue('general/locale/code'), 0, 2)),
                'currency' => $store->getCurrentCurrencyCode(),
                'feed_url' => $feedUrl,
                'feed_exists' => $feedExists,
            ];
        }

        return $this->resultFactory->create('json')->setData($config);
    }
}
