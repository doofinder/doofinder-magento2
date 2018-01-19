<?php

namespace Doofinder\Feed\Controller\Feed;

/**
 * Config controller
 */
class Config extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

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
        $this->productMetadata = $productMetadata;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->schedule = $schedule;
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Returns config json
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->helper->setJsonHeaders($this->getResponse());

        $config = [
            'platform' => [
                'name' => 'Magento',
                'edition' => $this->productMetadata->getEdition(),
                'version' => $this->productMetadata->getVersion(),
            ],
            'module' => [
                'version' => $this->helper->getModuleVersion(),
                'feed' => $this->storeManager->getStore()->getUrl('doofinder/feed'),
                'options' => [
                    'language' => [],
                ],
                'configuration' => [],
            ],
        ];

        foreach ($this->storeManager->getStores() as $store) {
            $storeCode = $store->getCode();
            $settings = $this->storeConfig->getStoreConfig($storeCode);

            if ($settings['enabled']) {
                $feedUrl = $this->schedule->getFeedFileUrl($storeCode, false);
                $feedExists = $this->schedule->isFeedFileExist($storeCode);
            } else {
                $feedUrl = $store->getUrl('doofinder/feed');
                $feedExists = true;
            }

            $config['module']['options']['language'][] = $storeCode;
            $config['module']['configuration'][$storeCode] = [
                'language' => strtoupper(substr($this->scopeConfig->getValue('general/locale/code'), 0, 2)),
                'currency' => $store->getCurrentCurrencyCode(),
                'feed_url' => $feedUrl,
                'feed_exists' => $feedExists,
            ];
        }

        return $this->resultFactory->create('json')->setData($config);
    }
}
