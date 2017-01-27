<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\Processor;

class AtomicUpdater extends Component implements Processor
{
    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    protected $_search;

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

        try {
            $this->_search->{$method}(array_map(function ($item) {
                return $item->getData();
            }, $items));
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
            throw $e;
        }
    }
}
