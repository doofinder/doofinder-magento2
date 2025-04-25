<?php

namespace Doofinder\Feed\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class UpdateOnClick extends Field
{
    /**
     * Template to be used
     *
     * @var string
     */
    protected $_template = 'Doofinder_Feed::System/Config/updateOnClickButton.phtml';

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Gets the URL of the update on save endpoint
     *
     * @return string
     */
    public function updateOnClick()
    {
        return $this->getUrl('doofinderfeed/integration/updateOnClick');
    }

    /**
     * Gets the HTML markup for the "Manual indexing" button
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData([
            'id' => 'manual-indexing', 'label' => __('Manual indexing'),
        ]);
        return $button->toHtml();
    }

    /**
     * Gets the HTML markup for the current element
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
