<?php

namespace Doofinder\Feed\Block\Adminhtml\Map;

/**
 * Class Additional
 * The class responsible for generating custom frontend model on Stores -> Configuration
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
    private function getAttributesRenderer()
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
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('field', ['label' => __('Field')]);
        $this->addColumn(
            'additional_attribute',
            ['label' => __('Attribute'), 'renderer' => $this->getAttributesRenderer()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * {@inheritDoc}
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $customAttribute = $row->getData('additional_attribute');

        $key = 'option_' . $this->getAttributesRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }
}
