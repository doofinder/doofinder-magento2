<?php

namespace Doofinder\Feed\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Doofinder Indexer handler factory.
 */
class IndexerHandlerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create doofinder indexer handler
     *
     * @param array $data
     * @return IndexerInterface
     */
    public function create(array $data = [])
    {
        $currentHandler = 'doofinder';
        $indexer = $this->_objectManager->create('Doofinder\Feed\Model\Indexer\IndexerHandler', $data);

        if (!$indexer instanceof IndexerInterface) {
            throw new \InvalidArgumentException(
                $currentHandler . ' indexer handler doesn\'t implement ' . IndexerInterface::class
            );
        }

        if ($indexer && !$indexer->isAvailable()) {
            throw new \LogicException(
                'Indexer handler is not available: ' . $currentHandler
            );
        }
        return $indexer;
    }
}
