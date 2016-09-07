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

        // Set mapped fields on item
        foreach ((array) $this->getMap() as $field => $definition) {
            if (!is_array($definition)) {
                $definition = [
                    'field' => $definition,
                ];
            }

            $item->setData($field, $this->processDefinition($definition));
        }
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
        return $this->getMapInstance($this->_context)->get($field);
    }

    /**
     * Get map instance
     *
     * @param \Magento\Framework\DataObject
     * @return \Doofinder\Feed\Generator\Map
     */
    protected function getMapInstance(\Magento\Framework\DataObject $context)
    {
        return $this->_mapFactory->create($context, $this->getData());
    }
}
