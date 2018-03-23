<?php

namespace Doofinder\Feed\Observer\AtomicUpdate;

use Magento\Framework\Search\Request\Dimension;

/**
 * Atomic update save observer
 */
class Save implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Doofinder\Feed\Search\Processor
     */
    private $processor;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Search\Processor $processor
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(
        \Doofinder\Feed\Search\Processor $processor,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig
    ) {
        $this->processor = $processor;
        $this->storeConfig = $storeConfig;
    }

    /**
     * Execute observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    // @codingStandardsIgnoreEnd
        // Do not proceed if atomic updates are disabled
        if (!$this->storeConfig->isAtomicUpdatesEnabled()) {
            return;
        }

        $product = $observer->getProduct();
        foreach ($this->storeConfig->getStoreCodes() as $storeCode) {
            $this->processor->update($storeCode, [$product]);
        }
    }
}
