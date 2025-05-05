<?php

namespace Doofinder\Feed\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;

class CleanIntegration extends Field
{
    /**
     * Template to be used
     *
     * @var string
     */
    protected $_template = 'Doofinder_Feed::System/Config/cleanIntegrationButton.phtml';

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Escaper $escaper,
        array $data = []
    ) {
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Make Escaper available to the template
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

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
            'id' => 'clean-integration',
            'label' => __('Reset All'),
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
