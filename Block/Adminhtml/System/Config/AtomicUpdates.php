<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config;

/**
 * Class AtomicUpdates
 */
class AtomicUpdates extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $indexer;

    /**
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\Indexer $indexer
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\Indexer $indexer,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->indexer = $indexer;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->storeConfig->isInternalSearchEnabled()) {
            $element->setDisabled(true);
            $element->setValue(!$this->indexer->isScheduled());
        }

        return parent::render($element);
    }
}
