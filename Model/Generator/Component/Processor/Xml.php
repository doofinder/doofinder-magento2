<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\ProcessorInterface;

/**
 * Xml component
 */
class Xml extends Component implements ProcessorInterface
{
    /**
     * @var \Sabre\Xml\Service
     */
    private $xmlService = null;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $fileIO = null;

    /**
     * @var \Magento\Framework\Filesystem\File\WriteFactory
     */
    private $fileWriteFactory = null;

    /**
     * @var string
     */
    private $feed = null;

    /**
     * Constructor
     *
     * @param \Sabre\Xml\Service $xmlService
     * @param \Doofinder\Feed\Helper\Data $helper
     * @param \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory
     * @param \Magento\Framework\Filesystem\Io\File $fileIO
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Sabre\Xml\Service $xmlService,
        \Doofinder\Feed\Helper\Data $helper,
        \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory,
        \Magento\Framework\Filesystem\Io\File $fileIO,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->xmlService = $xmlService;
        $this->helper = $helper;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->fileIO = $fileIO;
        parent::__construct($logger, $data);
    }

    /**
     * Process items
     *
     * @param  \Doofinder\Feed\Model\Generator\Item[] $items
     * @return void
     */
    public function process(array $items)
    {
        $writer = $this->xmlService->getWriter();

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
        return $this->feed;
    }

    /**
     * Initialize feed
     *
     * @param  \Sabre\Xml\Writer $writer
     * @return void
     */
    private function initializeFeed(\Sabre\Xml\Writer $writer)
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
     * @param  \Sabre\Xml\Writer $writer
     * @param  \Doofinder\Feed\Model\Generator\Item[] $items
     * @return void
     */
    private function generateFeed(\Sabre\Xml\Writer $writer, array $items)
    {
        if ($this->isStart()) {
            $writer->startDocument('1.0', 'UTF-8');

            $writer->writeRaw('<rss version="2.0">' . PHP_EOL);
            $writer->writeRaw('<channel>' . PHP_EOL);

            $writer->write([
                'title' => $this->createCdata('Product feed'),
                'link' => $this->createCdata($this->getFeedUrl()),
                'pubDate' => $this->createCdata(strftime('%a, %d %b %Y %H:%M:%S %Z')),
                'generator' => $this->createCdata($this->getModuleVersion()),
                'description' => $this->createCdata('Magento Product feed for Doofinder'),
            ]);
        }

        $writer->write($items);
    }

    /**
     * Finalize feed
     *
     * @param  \Sabre\Xml\Writer $writer
     * @return void
     */
    private function finalizeFeed(\Sabre\Xml\Writer $writer)
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
            $this->feed = $feed;
        }
    }

    /**
     * Check feed destination file
     *
     * @return void
     * @throws \Magento\Framework\Exception\StateException Feed file error.
     */
    private function checkDestinationFile()
    {
        $isStart = $this->isStart();
        $exists = $this->fileIO->fileExists($this->getDestinationFile());
        $isWriteable = $this->fileIO->isWriteable($this->getDestinationFile());

        if ($isStart && $exists) {
            throw new \Magento\Framework\Exception\StateException(
                __('Feed is starting but destination file exists')
            );
        } elseif (!$isStart && !$exists) {
            throw new \Magento\Framework\Exception\StateException(
                __('Feed is continuing but destination file does not exist')
            );
        } elseif (!$isStart && !$isWriteable) {
            throw new \Magento\Framework\Exception\StateException(
                __('Feed destination file is not writeable')
            );
        }
    }

    /**
     * Write feed to destination file
     *
     * @param  string $feed
     * @return void
     */
    private function writeToDestinationFile($feed)
    {
        $writer = $this->fileWriteFactory->create($this->getDestinationFile(), 'file', 'a');
        $writer->write($feed);
        $writer->flush();
    }

    /**
     * Is feed starting
     *
     * @return boolean
     */
    private function isStart()
    {
        return $this->hasStart() ? (bool) $this->getStart() : true;
    }

    /**
     * Is feed ending
     *
     * @return boolean
     */
    private function isEnd()
    {
        return $this->hasEnd() ? (bool) $this->getEnd() : true;
    }

    /**
     * Get feed url
     *
     * @return string
     */
    private function getFeedUrl()
    {
        return $this->helper->getBaseUrl() . 'doofinder/feed';
    }

    /**
     * Get module version
     *
     * @return string
     */
    private function getModuleVersion()
    {
        return 'Doofinder/' . $this->helper->getModuleVersion();
    }

    /**
     * Create Cdata element
     *
     * @param mixed $value
     * @return \Sabre\Xml\Element\Cdata
     */
    private function createCdata($value)
    {
        // @codingStandardsIgnoreStart
        return new \Sabre\Xml\Element\Cdata($value);
        // @codingStandardsIgnoreEnd
    }
}
