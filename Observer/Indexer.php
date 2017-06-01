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
    protected $_indexer;

    public function __construct(
        \Doofinder\Feed\Helper\Indexer $indexer
    ) {
        $this->_indexer = $indexer;
    }

    /**
     * Execute observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_indexer->shouldIndexInvalidate()) {
            $this->_indexer->invalidate();
        }
    }
}
