<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Represents "Delayed product updates" option on admin panel, found under following path:
 *
 * Stores > Configuration > Doofinder > Index Settings
 *
 * The option is set per store view.
 */
class DelayedUpdates extends Field
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
     * Prevents changing field value in case Doofinder is not set as internal search engine.
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
