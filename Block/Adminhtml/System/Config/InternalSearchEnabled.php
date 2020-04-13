<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config;

/**
 * Internal search enabled
 * Frontned model for "Is internal search enabled" field in Stores -> Configuration
 */
class InternalSearchEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Path to catalog config page section
     */
    const CATALOG_CONFIG_SECTION = 'catalog';

    /**
     * DOM Search engine fragment
     */
    const SEARCH_ENGINE_FRAGMENT = 'catalog_search-link';

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $enabled = $this->storeConfig->isInternalSearchEnabled();
        $element->setText(__('Internal search is ' . ($enabled ? 'enabled' : 'disabled') . '.'));

        if (!$enabled) {
            $url = $this->_urlBuilder->getUrl('*/*/*', [
                '_current' => true,
                'section' => self::CATALOG_CONFIG_SECTION,
                '_fragment' => self::SEARCH_ENGINE_FRAGMENT
            ]);
            $link = '<a href="' . $url . '">' . __('here') . '</a>';
            $element->setComment(__(
                'You can enable it %1 by choosing Doofinder in Search Engine field. '
                . 'Enabling internal search requires catalog\'s Update On Save index mode. '
                . 'Index mode will be automatically changed.',
                $link
            ));
        }

        return parent::_getElementHtml($element);
    }
}
