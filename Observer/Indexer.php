<?php

namespace Doofinder\Feed\Observer;

/**
 * Indexer observer
 */
class Indexer implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $indexer;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Helper\Indexer $indexer
     */
    public function __construct(
        \Doofinder\Feed\Helper\Indexer $indexer
    ) {
        $this->indexer = $indexer;
    }

    /**
     * Execute observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // phpcs:enable
        if ($this->indexer->shouldIndexInvalidate()) {
            $this->indexer->invalidate();
        }
    }
}
