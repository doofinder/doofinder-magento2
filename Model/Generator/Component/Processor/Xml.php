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
     * @var \Doofinder\Feed\Helper\Data
     */
    protected $_helper;

    /**
     * @var string
     */
    protected $_feed = null;

    /**
     * Constructor
     */
    public function __construct(
        \Sabre\Xml\Service $xmlService,
        \Doofinder\Feed\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_xmlService = $xmlService;
        $this->_helper = $helper;
        parent::__construct($logger, $data);
    }

    /**
     * Process items
     *
     * @param \Doofinder\Feed\Model\Generator\Item[]
     */
    public function process(array $items)
    {
        $writer = $this->_xmlService->getWriter();

        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');

        // Write to feed
        $this->_feed = $writer->write([
            'name' => 'rss',
            'attributes' => [
                'version' => '2.0'
            ],
            'value' => [
                'name' => 'channel',
                'value' => [
                    'title' => 'Product feed',
                    'link' => $this->getFeedUrl(),
                    'pubDate' => strftime('%a, %d %b %Y %H:%M:%S %Z'),
                    'generator' => $this->getModuleVersion(),
                    'description' => 'Magento Product feed for Doofinder',
                    $items
                ],
            ],
        ]);

        $this->_feed = $writer->outputMemory();
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

    /**
     * Get feed url
     *
     * @return string
     */
    protected function getFeedUrl()
    {
        return $this->_helper->getBaseUrl() . 'doofinder/feed';
    }

    /**
     * Get module version
     *
     * @return string
     */
    protected function getModuleVersion()
    {
        return 'Doofinder/' . $this->_helper->getModuleVersion();
    }
}
