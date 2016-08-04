<?php

namespace Doofinder\Feed\Model;

class Generator extends \Magento\Framework\DataObject
{
    /**
     * Gernerator fetcher factory
     *
     * @var Generator\Component\FetcherFactory
     */
    protected $_fetcherFactory = null;

    /**
     * Gernerator processor factory
     *
     * @var Generator\Component\ProcessorFactory
     */
    protected $_processorFactory = null;

    /**
     * Generator fetchers
     *
     * @var Generator\Component\Fetcher[]
     */
    protected $_fetchers = array();

    /**
     * Generator processors
     *
     * @var Generator\Component\Processor[]
     */
    protected $_processors = array();

    /**
     * Generator items
     *
     * @var Generator\Item[]
     */
    protected $_items = array();

    /**
     * @param Generator\Component\FetcherFactory $itemsFetcherFactory
     * @param Generator\Component\ProcessorFactory $itemsProcessorFactory
     * @param array $data
     */
    public function __construct(
        Generator\Component\FetcherFactory $fetcherFactory,
        Generator\Component\ProcessorFactory $processorFactory,
        array $data = []
    ) {
        $this->_fetcherFactory = $fetcherFactory;
        $this->_processorFactory = $processorFactory;
        parent::__construct($data);
    }


    /**
     * Run generator
     */
    public function run()
    {
        $this->initialize();

        // Fetch items
        $this->fetchItems();

        // Process items
        $this->processItems();
    }

    /**
     * Initialize generator
     *
     * @return Generator
     */
    protected function initialize()
    {
        // Create fetchers
        foreach ($this->getData('config/fetchers') as $class => $data) {
            $this->_fetchers[$class] = $this->_fetcherFactory->create(['data' => $data], $class);
        }

        // Create processors
        foreach ($this->getData('config/processors') as $class => $data) {
            $this->_processors[$class] = $this->_processorFactory->create(['data' => $data], $class);
        }

        return $this;
    }

    /**
     * Fetch items
     *
     * @return Generator\Item[]
     * @return Generator
     */
    protected function fetchItems()
    {
        $this->_items = array();

        foreach ($this->_fetchers as $fetcher) {
            $this->_items = array_merge($this->_items, $fetcher->fetch());
        }

        return $this;
    }

    /**
     * Process items
     *
     * @return Generator
     */
    protected function processItems()
    {
        foreach ($this->_processors as $processor) {
            $processor->process($this->_items);
        }

        return $this;
    }

    /**
     * Get processor
     *
     * @param string
     * @return \Doofinder\Feed\Model\Generator\Component\Processor
     */
    public function getProcessor($class)
    {
        return isset($this->_processors[$class]) ? $this->_processors[$class] : null;
    }
}
