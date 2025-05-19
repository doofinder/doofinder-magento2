<?php

namespace Doofinder\Feed\Block\System\Config;

use Doofinder\Feed\Helper\StoreConfig;
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
     * @var StoreConfig
     */
    protected $storeConfig;

    /**
     * StoreViewTable constructor.
     *
     * @param Context $context
     * @param StoreConfig $storeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreConfig $storeConfig,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

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
            'disabled' => null === $this->getIntegrationId() ? 'disabled' : '',
        ]);
        return $button->toHtml();
    }

    /**
     * Retrieve the integration ID.
     *
     * @return string|null
     */
    public function getIntegrationId()
    {
        return $this->storeConfig->getValueFromConfig(
            StoreConfig::INTEGRATION_ID_CONFIG
        );
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
