<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\ProcessorInterface;

/**
 * Mapper component
 */
class Mapper extends Component implements ProcessorInterface
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $context = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\Map
     */
    private $map = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\MapFactory
     */
    private $mapFactory = null;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Model\Generator\MapFactory $mapFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Model\Generator\MapFactory $mapFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->mapFactory = $mapFactory;
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
        foreach ($items as $item) {
            $this->processItem($item);
        }
    }

    /**
     * Process item
     *
     * @param  \Doofinder\Feed\Model\Generator\Item $item
     * @return void
     */
    private function processItem(\Doofinder\Feed\Model\Generator\Item $item)
    {
        $this->item = $item;
        $this->context = $this->item->getContext();
        $this->map = $this->getMapInstance($this->item);

        // Before
        $this->map->before();

        // Set mapped fields on item
        foreach ((array) $this->getMap() as $field => $definition) {
            if (!is_array($definition)) {
                $definition = [
                    'field' => $definition,
                ];
            }

            $item->setData($field, $this->processDefinition($definition));
        }

        // After
        $this->map->after();
    }

    /**
     * Get mapped field value
     *
     * @param  array $definition
     * @return mixed
     */
    private function processDefinition(array $definition)
    {
        $field = $definition['field'];
        $value = $this->map->get($field);

        switch ($field) {
            case 'boost':
                $value = is_array($value) ? max($value) : $value;
                break;
        }

        return $value;
    }

    /**
     * Get map instance
     *
     * @param  \Doofinder\Feed\Model\Generator\Item $item
     * @return \Doofinder\Feed\Model\Generator\Map
     */
    private function getMapInstance(\Doofinder\Feed\Model\Generator\Item $item)
    {
        return $this->mapFactory->create($item, $this->getData());
    }
}
