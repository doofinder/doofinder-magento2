<?php

namespace Doofinder\Feed\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;


class CreateSearchEngine extends Field
{


    protected $_template = 'Doofinder_Feed::System/Config/createSearchEngine.phtml';
    public function __construct(Context $context, array $data = [],)
    {
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    public function getAjaxUrl()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->getUrl("doofinderfeed/integration/createSearchEngine/store/$storeId");
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'id' => 'create-search-engine',
            'label' => __('Create Search Engine'),
        ]);
        return $button->toHtml();
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
