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
    protected $_attributesRenderer;

    /**
     * Returns renderer for additional attributes.
     *
     * @return \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
     */
    protected function _getAttributesRenderer()
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
     * Prepare to render.
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('label', array('label' => __('Label')));
        $this->addColumn('field', array('label' => __('Field')));
        $this->addColumn(
            'additional_attribute',
            ['label' => __('Attribute'), 'renderer' => $this->_getAttributesRenderer()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $customAttribute = $row->getData('additional_attribute');

        $key = 'option_' . $this->_getAttributesRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }
}
