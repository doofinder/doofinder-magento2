<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\ProcessorInterface;

class AtomicUpdater extends Component implements ProcessorInterface
{
    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $_search;

    /**
     * Constructor
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Doofinder\Feed\Helper\Search $search,
        array $data = []
    ) {
        $this->_search = $search;
        parent::__construct($logger, $data);
    }

    /**
     * Process items
     *
     * @param \Doofinder\Feed\Model\Generator\Item[]
     */
    public function process(array $items)
    {
        $method = $this->getData('action') . 'DoofinderItems';

        $data = array_map(function ($item) {
            return array_filter($item->getData());
        }, $items);

        try {
            $this->_search->{$method}($data);
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
            throw $e;
        }
    }
}
