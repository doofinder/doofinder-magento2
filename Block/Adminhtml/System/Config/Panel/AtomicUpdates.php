<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

class AtomicUpdates extends Message
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
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_storeConfig = $storeConfig;
        $this->_storeManager = $storeManager;
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

        $messages = array();

        foreach ($storeCodes as $storeCode) {
            $atomicUpdatesEnabled = $this->_storeConfig->isAtomicUpdatesEnabled();

            if (!$atomicUpdatesEnabled) {
                $message = __('Atomic updates are <strong>disabled</strong>.');
            } elseif ($atomicUpdatesEnabled) {
                $message = __(
                    'Atomic updates are <strong>enabled</strong>. ' .
                    'Your products will be automatically indexed ' .
                    'when they are created, updated or deleted.'
                );
            }

            $messages[$this->_storeManager->getStore($storeCode)->getName()] = $message;
        }

        if (count(array_unique($messages)) == 1) {
            return reset($messages);
        }

        $html = '<ul>';
        foreach ($messages as $name => $message) {
            $html .= '<li><strong>' . $name . ':</strong><p>' . $message . '</p></li>';
        }
        $html .= '</ul>';

        return $html;
    }
}
