<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\Processor;

class Mapper extends Component implements Processor
{
    /**
     * Item data
     *
     * @var array
     */
    protected $_itemData = [];

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
        $this->_itemData = $item->getData();

        // Purge item data
        $item->setData([]);

        // Set mapped fields on item
        foreach ((array) $this->getMap() as $field => $definition) {
            if (!is_array($definition)) {
                $definition = [
                    'field' => $definition,
                ];
            }

            $item->setData($field, $this->getMappedFieldValue($definition));
        }
    }

    /**
     * Get mapped field value
     *
     * @param array
     * @return mixed
     */
    protected function getMappedFieldValue(array $definition)
    {
        $field = $definition['field'];
        $value = isset($this->_itemData[$field]) ? $this->_itemData[$field] : null;

        return $value;
    }
}
