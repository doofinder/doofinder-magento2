<?php

namespace Doofinder\Feed\Block\Adminhtml\Map;

/**
 * Class Additional
 *
 * @package Doofinder\Feed\Block\Adminhtml\Map
 */
class Additional extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var $_attributesRenderer \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes;
     */
    private $_attributesRenderer;

    /**
     * Returns renderer for additional attributes.
     *
     * @return \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
     */
    private function _getAttributesRenderer()
    {
        if (!$this->_attributesRenderer) {
            $this->_attributesRenderer = $this->getLayout()->createBlock(
                '\Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->_attributesRenderer;
    }

    /**
     * {@inheritDoc}
     * @codingStandardsIgnoreStart
     */
    protected function _prepareToRender()
    {
    // @codingStandardsIgnoreEnd
        $this->addColumn('label', ['label' => __('Label')]);
        $this->addColumn('field', ['label' => __('Field')]);
        $this->addColumn(
            'additional_attribute',
            ['label' => __('Attribute'), 'renderer' => $this->_getAttributesRenderer()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * {@inheritDoc}
     * @codingStandardsIgnoreStart
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
    // @codingStandardsIgnoreEnd
        $options = [];
        $customAttribute = $row->getData('additional_attribute');

        $key = 'option_' . $this->_getAttributesRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }
}
