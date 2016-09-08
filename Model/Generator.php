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
     * @var \Magento\Framework\Event\ManagerInteface
     */
    protected $_eventManager = null;

    /**
     * Generator items
     *
     * @var Generator\Item[]
     */
    protected $_items = array();

    /**
     * @param Generator\Component\FetcherFactory $itemsFetcherFactory
     * @param Generator\Component\ProcessorFactory $itemsProcessorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param array $data
     */
    public function __construct(
        Generator\Component\FetcherFactory $fetcherFactory,
        Generator\Component\ProcessorFactory $processorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    ) {
        $this->_fetcherFactory = $fetcherFactory;
        $this->_processorFactory = $processorFactory;
        $this->_eventManager = $eventManager;
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

        // Dispatch event doofinder_feed_generator_initialized
        $this->dispatch('initialized');

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

        // Dispatch event doofinder_feed_generator_items_fetched
        $this->dispatch('items_fetched');

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
            /**
             * Run processor only on not skipped items
             * @notice Compatible with PHP5.3+
             */
            $items = array_filter($this->_items, function ($item) {
                return !$item->isSkip();
            });

            $processor->process($items);
        }

        // Dispatch event doofinder_feed_generator_items_processed
        $this->dispatch('items_processed');

        return $this;
    }

    /**
     * Get fetchers
     *
     * @return \Doofinder\Feed\Model\Generator\Component\Fetcher|array
     */
    public function getFetcher($class = null)
    {
        if ($class === null) {
            return $this->_fetchers;
        }

        return isset($this->_fetchers[$class]) ? $this->_fetchers[$class] : null;
    }

    /**
     * Get processor
     *
     * @param string
     * @return \Doofinder\Feed\Model\Generator\Component\Processor|array
     */
    public function getProcessor($class = null)
    {
        if ($class === null) {
            return $this->_processors;
        }

        return isset($this->_processors[$class]) ? $this->_processors[$class] : null;
    }

    /**
     * Dispatch an event
     *
     * @param string
     */
    protected function dispatch($eventName)
    {
        $this->_eventManager->dispatch('doofinder_feed_generator_' . $eventName, [
            'generator' => $this,
        ]);
    }
}
