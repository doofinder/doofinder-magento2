<?php

namespace Doofinder\Feed\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class CleanIntegration extends Field
{
    /**
     * Template to be used
     *
     * @var string
     */
    protected $_template = 'Doofinder_Feed::System/Config/cleanIntegrationButton.phtml';

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Gets the AJAX URL
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('doofinderfeed/integration/cleanIntegration');
    }

    /**
     * Gets the HTML markup for the "Reset All" button
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData([
            'id' => 'clean-integration', 'label' => __('Reset All'),
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
