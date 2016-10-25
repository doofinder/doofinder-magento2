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
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_fileIO = null;

    /**
     * @var \Magento\Framework\Filesystem\File\WriteFactory
     */
    protected $_fileWriteFactory = null;

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
        \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory,
        \Magento\Framework\Filesystem\Io\File $fileIO,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_xmlService = $xmlService;
        $this->_helper = $helper;
        $this->_fileWriteFactory = $fileWriteFactory;
        $this->_fileIO = $fileIO;
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

        // Initialize feed
        $this->initializeFeed($writer);

        // Write to feed
        $this->generateFeed($writer, $items);

        // Finalize feed
        $this->finalizeFeed($writer);
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
     * Initialize feed
     *
     * @param \Sabre\Xml\Writer
     */
    protected function initializeFeed(\Sabre\Xml\Writer $writer)
    {
        if ($this->getDestinationFile()) {
            $this->checkDestinationFile();
        }

        $writer->openMemory();
        $writer->setIndent(true);
    }

    /**
     * Generate feed
     *
     * @param \Sabre\Xml\Writer
     * @param \Doofinder\Feed\Model\Generator\Item[]
     */
    protected function generateFeed(\Sabre\Xml\Writer $writer, array $items)
    {
        if ($this->isStart()) {
            $writer->startDocument('1.0', 'UTF-8');

            $writer->writeRaw('<rss version="2.0">' . PHP_EOL);
            $writer->writeRaw('<channel>' . PHP_EOL);

            $writer->write([
                'title' => new \Sabre\Xml\Element\Cdata('Product feed'),
                'link' => new \Sabre\Xml\Element\Cdata($this->getFeedUrl()),
                'pubDate' => new \Sabre\Xml\Element\Cdata(strftime('%a, %d %b %Y %H:%M:%S %Z')),
                'generator' => new \Sabre\Xml\Element\Cdata($this->getModuleVersion()),
                'description' => new \Sabre\Xml\Element\Cdata('Magento Product feed for Doofinder'),
            ]);
        }

        $writer->write($items);
    }

    /**
     * Finalize feed
     *
     * @param \Sabre\Xml\Writer
     */
    protected function finalizeFeed(\Sabre\Xml\Writer $writer)
    {
        if ($this->isEnd()) {
            $writer->writeRaw('</channel>' . PHP_EOL);
            $writer->writeRaw('</rss>' . PHP_EOL);
        }

        $feed = $writer->outputMemory();

        if ($this->getDestinationFile()) {
            $this->checkDestinationFile();
            $this->writeToDestinationFile($feed);
        } else {
            $this->_feed = $feed;
        }
    }

    /**
     * Check feed destination file
     */
    protected function checkDestinationFile()
    {
        $isStart = $this->isStart();
        $exists = $this->_fileIO->fileExists($this->getDestinationFile());
        $isWriteable = $this->_fileIO->isWriteable($this->getDestinationFile());

        if ($isStart && $exists) {
            throw new \Magento\Framework\Exception\StateException(
                new \Magento\Framework\Phrase('Feed is starting but destination file exists')
            );
        } else if (!$isStart && !$exists) {
            throw new \Magento\Framework\Exception\StateException(
                new \Magento\Framework\Phrase('Feed is continuing but destination file does not exist')
            );
        } else if (!$isStart && !$isWriteable) {
            throw new \Magento\Framework\Exception\StateException(
                new \Magento\Framework\Phrase('Feed destination file is not writeable')
            );
        }
    }

    /**
     * Write feed to destination file
     *
     * @param  string
     */
    protected function writeToDestinationFile($feed)
    {
        $writer = $this->_fileWriteFactory->create($this->getDestinationFile(), 'file', 'a');
        $writer->write($feed);
        $writer->flush();
    }

    /**
     * Is feed starting
     *
     * @param  boolean
     */
    protected function isStart()
    {
        return $this->hasStart() ? (bool) $this->getStart() : true;
    }

    /**
     * Is feed ending
     *
     * @param  boolean
     */
    protected function isEnd()
    {
        return $this->hasEnd() ? (bool) $this->getEnd() : true;
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
