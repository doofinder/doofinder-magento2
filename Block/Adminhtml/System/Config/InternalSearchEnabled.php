<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config;

/**
 * Internal search enabled
 */
class InternalSearchEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Catalog config page section
     */
    const CATALOG_CONFIG_SECTION = 'catalog';

    /**
     * Search engine fragment
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
     * @codingStandardsIgnoreStart
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
    // @codingStandardsIgnoreEnd
        $enabled = $this->storeConfig->isInternalSearchEnabled();
        $element->setText(__('Internal search is ' . ($enabled ? 'enabled' : 'disabled') . '.'));

        if (!$enabled) {
            $url = $this->_urlBuilder->getUrl('*/*/*', [
                '_current' => true,
                'section' => self::CATALOG_CONFIG_SECTION,
                '_fragment' => self::SEARCH_ENGINE_FRAGMENT
            ]);
            $link = '<a href="' . $url . '">' . __('here') . '</a>';
            $element->setComment(__('You can enable it %1 by choosing Doofinder in Search Engine field.', $link));
        }

        return parent::_getElementHtml($element);
    }
}
