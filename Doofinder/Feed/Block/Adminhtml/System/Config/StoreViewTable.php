<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
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
     * @var Escaper
     */
    protected $escaper;

    /**
     * StoreViewTable constructor.
     *
     * @param Context $context
     * @param Website $website
     * @param StoreConfig $storeConfig
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Website $website,
        StoreConfig $storeConfig,
        Escaper $escaper,
        array $data = []
    ) {
        $this->website = $website;
        $this->storeConfig = $storeConfig;
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

    /**
     * Retrieve the installation ID for a given store group.
     *
     * @param Group $group
     * @return string|null
     */
    public function getInstallationId(Group $group)
    {
        return $this->storeConfig->getValueFromConfig(
            StoreConfig::DISPLAY_LAYER_INSTALLATION_ID,
            ScopeInterface::SCOPE_GROUP,
            (int)$group->getId()
        );
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
     * Generate the AJAX URL for sync/migrate call.
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl("doofinderfeed/integration/createStore");
    }

    /**
     * Generate the initial setup URL.
     *
     * @return string
     */
    public function getInitialSetupUrl()
    {
        return $this->getUrl("doofinderfeed/setup/index");
    }
}
