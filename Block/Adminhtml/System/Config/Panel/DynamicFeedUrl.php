<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

class DynamicFeedUrl extends Message
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_frontendUrlBuilder;

    /**
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $frontendUrlBuilder,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_storeConfig = $storeConfig;
        $this->_storeManager = $storeManager;
        $this->_frontendUrlBuilder = $frontendUrlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Get element text
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function getText(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $storeCodes = $this->_storeConfig->getStoreCodes();

        $html = '';
        foreach ($storeCodes as $storeCode) {
            $store = $this->_storeManager->getStore($storeCode);
            $config = $this->_storeConfig->getStoreConfig($storeCode);

            $password = $config['password'];
            $params = array('language' => $store->getCode());

            if ($password) {
                $params['password'] = $password;
            }

            $this->_frontendUrlBuilder->setScope($store->getId());
            $url = $this->_frontendUrlBuilder->getUrl('doofinder/feed', [
                '_nosid' => true
            ] + $params);
            $anchor = '<a href="' . $url . '">' . $url . '</a>';

            $html .= '<p><strong>' . $store->getName() . ':</strong></p><p>' . $anchor . '</p>';
        }

        $html .= '<p>';
        $html .= __(
            'If cron feed doesn\'t work for you, use these URLs ' .
            'to dynamically index your content from Doofinder. ' .
            'Contact support if you need help.'
        );
        $html .= '</p>';

        return $html;
    }
}
