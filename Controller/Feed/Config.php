<?php

namespace Doofinder\Feed\Controller\Feed;

/**
 * Class Config
 *
 * @package Doofinder\Feed\Controller\Feed
 */
class Config extends \Doofinder\Feed\Controller\Base
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

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
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Doofinder\Feed\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        $this->_productMetadata = $productMetadata;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_storeConfig = $storeConfig;
        $this->_schedule = $schedule;
        $this->_filesystem = $filesystem;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $jsonResultFactory);
    }

    /**
     * Execute.
     */
    public function execute()
    {
        $this->_setJsonHeaders();

        $config = [
            'platform' => [
                'name' => 'Magento',
                'edition' => $this->_productMetadata->getEdition(),
                'version' => $this->_productMetadata->getVersion(),
            ],
            'module' => [
                'version' => $this->_helper->getModuleVersion(),
                'feed' => $this->_storeManager->getStore()->getUrl('doofinder/feed'),
                'options' => array(
                    'language' => [],
                ),
                'configuration' => [],
            ],
        ];

        foreach ($this->_storeManager->getStores() as $store) {
            $storeCode = $store->getCode();
            $settings = $this->_storeConfig->getStoreConfig($storeCode);

            if ($settings['enabled']) {
                $feedUrl = $this->_schedule->getFeedFileUrl($storeCode);
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

        return $this->_jsonResultFactory->create()->setData($config);
    }
}
