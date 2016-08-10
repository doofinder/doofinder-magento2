<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config;

/**
 * Class GenerateButton
 *
 * @package Doofinder\Feed\Block\Adminhtml\System\Config
 */

class GenerateButton extends \Magento\Config\Block\System\Config\Form\Field
{
    const GENERATE_URL = 'doofinder/feed/generate';

    /**
     * GenerateButton constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Set template file for button.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/generatebutton.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $buttonLabel = $this->getButtonLabel($element);

        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl(self::GENERATE_URL),
            ]
        );

        return $this->_toHtml();
    }

    protected function getButtonLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();

        if (!empty($originalData['button_label'])) {
            return $originalData['button_label'];
        } else {
            return 'Start Feed Generation Now';
        }
    }
}