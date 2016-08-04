<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\Processor;

class Xml extends Component implements Processor
{
    /**
     * @var \Sabre\Xml\Service
     */
    protected $_xmlService = null;

    /**
     * @var string
     */
    protected $_feed = null;

    /**
     * Constructor
     */
    public function __construct(
        \Sabre\Xml\Service $xmlService
    ) {
        $this->_xmlService = $xmlService;
    }

    /**
     * Process items
     *
     * @param \Doofinder\Feed\Model\Generator\Item[]
     */
    public function process(array $items)
    {
        // Write to feed
        $this->_feed = $this->_xmlService->write('feed', $items);
    }

    /**
     * Get generate feed
     *
     * @return string|null
     */
    public function getFeed()
    {
        return $this->_feed;
    }
}
