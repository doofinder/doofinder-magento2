<?php

namespace Doofinder\Feed\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class CreateSearchEngine extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Doofinder_Feed::System/Config/createSearchEngine.phtml';

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the URL for the AJAX request.
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->getUrl("doofinderfeed/integration/createSearchEngine/store/$storeId");
    }

    /**
     * Get the HTML for the button.
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData([
            'id' => 'create_search_engine',
            'label' => __('Create Search Engine'),
        ]);
        return $button->toHtml();
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
