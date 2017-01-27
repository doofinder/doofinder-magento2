<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\Processor;

class Mapper extends Component implements Processor
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    protected $_item = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_context = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\Map
     */
    protected $_map = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\MapFactory
     */
    protected $_mapFactory = null;

    public function __construct(
        \Doofinder\Feed\Model\Generator\MapFactory $mapFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_mapFactory = $mapFactory;
        parent::__construct($logger, $data);
    }

    /**
     * Process items
     *
     * @param \Doofinder\Feed\Model\Generator\Item[]
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
     * @param \Doofinder\Feed\Model\Generator\Item
     */
    protected function processItem(\Doofinder\Feed\Model\Generator\Item $item)
    {
        $this->_item = $item;
        $this->_context = $this->_item->getContext();
        $this->_map = $this->getMapInstance($this->_item);

        // Before
        $this->_map->before();

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
        $this->_map->after();
    }

    /**
     * Get mapped field value
     *
     * @param array
     * @return mixed
     */
    protected function processDefinition(array $definition)
    {
        $field = $definition['field'];
        $value = $this->_map->get($field);

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
     * @param \Doofinder\Feed\Model\Generator\Item
     * @return \Doofinder\Feed\Model\Generator\Map
     */
    protected function getMapInstance(\Doofinder\Feed\Model\Generator\Item $item)
    {
        return $this->_mapFactory->create($item, $this->getData());
    }
}
