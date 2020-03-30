<?php

namespace Doofinder\Feed\Block\Adminhtml\Map;

/**
 * Class Additional
 */
class Additional extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
     */
    private $attributesRenderer;

    /**
     * Returns renderer for additional attributes.
     *
     * @return \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
     */
    private function _getAttributesRenderer()
    {
        if (!$this->attributesRenderer) {
            $this->attributesRenderer = $this->getLayout()->createBlock(
                \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->attributesRenderer;
    }

    /**
     * {@inheritDoc}
     * @codingStandardsIgnoreStart
     */
    protected function _prepareToRender()
    {
    // @codingStandardsIgnoreEnd
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
     *
     * @param \Magento\Framework\DataObject $row
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
