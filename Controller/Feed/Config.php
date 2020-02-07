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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Doofinder\Feed\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->productMetadata = $productMetadata;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
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

            $config['module']['options']['language'][] = $storeCode;
            $config['module']['configuration'][$storeCode] = [
                'language' => strtoupper(substr($this->scopeConfig->getValue('general/locale/code'), 0, 2)),
                'currency' => $store->getCurrentCurrencyCode(),
            ];
        }

        return $this->resultFactory->create('json')->setData($config);
    }
}
