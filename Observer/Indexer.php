<?php

namespace Doofinder\Feed\Observer;

/**
 * Class Indexer
 *
 * @package Doofinder\Feed\Observer
 */
class Indexer implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $_indexer;

    public function __construct(
        \Doofinder\Feed\Helper\Indexer $indexer
    ) {
        $this->_indexer = $indexer;
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
        if ($this->_indexer->shouldIndexInvalidate()) {
            $this->_indexer->invalidate();
        }
    }
}
