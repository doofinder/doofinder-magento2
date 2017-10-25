<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

/**
 * Class ViewLogButton
 *
 * @package Doofinder\Feed\Block\Adminhtml\System\Config\Panel
 */

class ViewLogButton extends \Magento\Config\Block\System\Config\Form\Field
{
    const VIEW_LOG_URL = 'doofinder/feed/log';

    /**
     * Set template file for button.
     *
     * @return $this
     * @codingStandardsIgnoreStart
     */
    protected function _prepareLayout()
    {
    // @codingStandardsIgnoreEnd
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/panel/viewlogbutton.phtml');
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
     * @codingStandardsIgnoreStart
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
    // @codingStandardsIgnoreEnd
        $buttonLabel = $this->getButtonLabel($element);

        $urlData = ($storeId = $this->_request->getParam('store')) ? ['store' => $storeId] : [];

        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'redirect_url' => $this->_urlBuilder->getUrl(self::VIEW_LOG_URL, $urlData),
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Get button label
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    private function getButtonLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();

        if (!empty($originalData['button_label'])) {
            return $originalData['button_label'];
        } else {
            return 'View Log';
        }
    }
}
