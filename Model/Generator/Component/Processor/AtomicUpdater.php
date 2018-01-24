<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\ProcessorInterface;

/**
 * Atomic updater component
 */
class AtomicUpdater extends Component implements ProcessorInterface
{
    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

    /**
     * Constructor
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Doofinder\Feed\Helper\Search $search
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Doofinder\Feed\Helper\Search $search,
        array $data = []
    ) {
        $this->search = $search;
        parent::__construct($logger, $data);
    }

    /**
     * Process items
     *
     * @param  \Doofinder\Feed\Model\Generator\Item[] $items
     * @return void
     * @throws \Exception Error.
     */
    public function process(array $items)
    {
        $data = array_map(function ($item) {
            return array_filter($item->getData());
        }, $items);

        try {
            $this->search->updateDoofinderItems($data);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw $e;
        }
    }
}
