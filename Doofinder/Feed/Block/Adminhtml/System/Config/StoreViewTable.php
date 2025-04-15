<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\Group;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Website;

class StoreViewTable extends Field
{
    /**
     * Set template to our custom phtml file
     *
     * @var string
     */
    protected $_template = 'Doofinder_Feed::System/Config/storeViewTable.phtml';

    /**
     * @var Website
     */
    protected $website;

    /**
     * @var StoreConfig
     */
    protected $storeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Website $website
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Website $website,
        StoreConfig $storeConfig,
        array $data = []
    ) {
        $this->website = $website;
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Render block using template file.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // You can pass additional variables to the template if needed.
        return $this->toHtml();
    }

    /**
     * Retrieve available stores
     *
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function getGroups()
    {
        $websiteId = (int)$this->getRequest()->getParam('website');
        return $this->website->load($websiteId)->getGroupCollection()->setOrder('group_id', 'ASC');
    }

    public function getInstallationId(Group $group)
    {
        return $this->storeConfig->getValueFromConfig(StoreConfig::DISPLAY_LAYER_INSTALLATION_ID, ScopeInterface::SCOPE_GROUP, (int)$group->getId());
    }

    /**
     * Generate the AJAX URL for sync/migrate call.
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl("doofinderfeed/integration/createStore");
    }
}
