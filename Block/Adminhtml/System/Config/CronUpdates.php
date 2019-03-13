<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class CronUpdates
 */
class CronUpdates extends Field
{
    /**
     * @var StoreConfig $storeConfig
     */
    private $storeConfig;

    /**
     * A constructor.
     *
     * @param StoreConfig $storeConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        StoreConfig $storeConfig,
        Context $context,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (!$this->storeConfig->isInternalSearchEnabled()) {
            $element->setDisabled(true);
        }

        return parent::render($element);
    }
}
