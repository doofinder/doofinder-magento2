<?php

namespace Doofinder\Feed\Model;

/**
 * Generator model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Generator extends \Magento\Framework\DataObject
{
    const CATEGORY_SEPARATOR = '%%';
    const CATEGORY_TREE_SEPARATOR = '>';
    const VALUE_SEPARATOR = '/';

    /**
     * Gernerator fetcher factory
     *
     * @var Generator\Component\FetcherFactory
     */
    private $fetcherFactory = null;

    /**
     * Gernerator processor factory
     *
     * @var Generator\Component\ProcessorFactory
     */
    private $processorFactory = null;

    /**
     * Generator fetchers
     *
     * @var Generator\Component\Fetcher[]
     */
    private $fetchers = [];

    /**
     * Generator processors
     *
     * @var Generator\Component\Processor[]
     */
    private $processors = [];

    /**
     * @var \Magento\Framework\Event\ManagerInteface
     */
    private $eventManager = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Generator items
     *
     * @var Generator\Item[]
     */
    private $items = [];

    /**
     * @param Generator\Component\FetcherFactory $fetcherFactory
     * @param Generator\Component\ProcessorFactory $processorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Generator\Component\FetcherFactory $fetcherFactory,
        Generator\Component\ProcessorFactory $processorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->fetcherFactory = $fetcherFactory;
        $this->processorFactory = $processorFactory;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        parent::__construct($data);
    }

    /**
     * Run generator
     *
     * @return void
     */
    public function run()
    {
        $this->logger->debug(__('Generator run started'));

        $this->initialize();

        // Fetch items
        $this->fetchItems();

        // Process items
        $this->processItems();

        $this->logger->debug(__('Generator run finished'));
    }

    /**
     * Initialize generator
     *
     * @return Generator
     */
    private function initialize()
    {
        // Create fetchers
        foreach ($this->getData('config/fetchers') as $class => $data) {
            $this->fetchers[$class] = $this->fetcherFactory->create([
                'data' => $data,
                'logger' => $this->logger,
            ], $class);
        }

        // Create processors
        foreach ($this->getData('config/processors') as $class => $data) {
            $this->processors[$class] = $this->processorFactory->create([
                'data' => $data,
                'logger' => $this->logger,
            ], $class);
        }

        // Dispatch event doofinder_feed_generator_initialized
        $this->dispatch('initialized');

        return $this;
    }

    /**
     * Fetch items
     *
     * @return Generator
     */
    private function fetchItems()
    {
        $this->items = [];

        foreach ($this->fetchers as $fetcher) {
            $this->logger->debug(__('Fetching items with %1', get_class($fetcher)));
            $this->items = array_merge($this->items, $fetcher->fetch());
        }

        // Dispatch event doofinder_feed_generatoritems_fetched
        $this->dispatch('items_fetched');

        return $this;
    }

    /**
     * Process items
     *
     * @return Generator
     */
    private function processItems()
    {
        foreach ($this->processors as $processor) {
            $this->logger->debug(__('Processing items with %1', get_class($processor)));

            /**
             * Run processor only on not skipped items
             * @notice Compatible with PHP5.3+
             */
            $items = array_filter($this->items, function ($item) {
                return !$item->isSkip();
            });

            if ($processor instanceof \Doofinder\Feed\Model\Generator\Component\Processor\AtomicUpdater) {
                foreach ($items as $item) {
                    if (!$item->getData('best_price')) {
                        $item->setData('best_price', $this->getBestPriceValue($item));
                    }
                }
            }

            $processor->process($items);
        }

        // Dispatch event doofinder_feed_generatoritems_processed
        $this->dispatch('items_processed');

        return $this;
    }

    /**
     * Get best_price value as a smallest of price and sale_prica
     *
     * @param \Doofinder\Feed\Model\Generator\Item $item
     * @return string|null
     */
    private function getBestPriceValue(\Doofinder\Feed\Model\Generator\Item $item)
    {
        $salePrice = $item->getData('sale_price');
        $price = $item->getData('price');

        if ($salePrice && $price) {
            return min($salePrice, $price);
        }

        if ($price) {
            return $price;
        }

        return null;
    }

    /**
     * Get fetchers
     *
     * @param  string $class
     * @return \Doofinder\Feed\Model\Generator\Component\Fetcher|array
     */
    public function getFetcher($class = null)
    {
        if ($class === null) {
            return $this->fetchers;
        }

        return isset($this->fetchers[$class]) ? $this->fetchers[$class] : null;
    }

    /**
     * Get processor
     *
     * @param  string $class
     * @return \Doofinder\Feed\Model\Generator\Component\Processor|array
     */
    public function getProcessor($class = null)
    {
        if ($class === null) {
            return $this->processors;
        }

        return isset($this->processors[$class]) ? $this->processors[$class] : null;
    }

    /**
     * Dispatch an event
     *
     * @param  string $eventName
     * @return void
     */
    private function dispatch($eventName)
    {
        $this->eventManager->dispatch('doofinder_feed_generator_' . $eventName, [
            'generator' => $this,
        ]);
    }
}
