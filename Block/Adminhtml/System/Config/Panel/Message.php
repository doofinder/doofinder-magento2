<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

class Message extends \Magento\Config\Block\System\Config\Form\Field
{
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
        $element->setData('text', $this->getText($element));
        return parent::_getElementHtml($element);
    }

    /**
     * Render scope label
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @codingStandardsIgnoreStart
     */
    protected function _renderScopeLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
    // @codingStandardsIgnoreEnd
        return '';
    }
}
