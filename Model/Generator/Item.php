<?php

namespace Doofinder\Feed\Model\Generator;

class Item extends \Magento\Framework\DataObject implements \Sabre\Xml\XmlSerializable
{
    /**
     * @var \Magento\Framework\DataObject|null
     */
    private $_context = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item[]
     */
    private $_associates = [];

    /**
     * @var boolean
     */
    private $_skip = false;

    /**
     * Serialize item to an XML
     *
     * @param \Sabre\Xml\Writer
     */
    public function xmlSerialize(\Sabre\Xml\Writer $writer)
    {
        $writer->write([
            'name' => 'item',
            'value' => $this->convertDataToXmlProperty($this->getData())
        ]);
    }

    /**
     * Convert data into xml valid property
     *
     * @param array
     * @return array
     */
    private function convertDataToXmlProperty($data)
    {
        $properties = [];

        foreach ($data as $key => $value) {
            if (!$value) {
                continue;
            } elseif (is_array($value)) {
                if ($this->isAssocArray($value)) {
                    $value = $this->convertDataToXmlProperty($value);
                } else {
                    $value = implode(\Doofinder\Feed\Model\Generator::VALUE_SEPARATOR, $value);
                }
            }

            if (!is_array($value) && !is_a($value, '\Sabre\Xml\XmlSerializable')) {
                $value = $this->createCdata($value);
            }

            $properties[] = [
                'name' => $key,
                'value' => $value,
            ];
        }

        return $properties;
    }

    /**
     * Set item context
     *
     * @param \Magento\Framework\DataObject
     * @return Item
     */
    public function setContext(\Magento\Framework\DataObject $context)
    {
        $this->_context = $context;
        return $this;
    }

    /**
     * Get item context
     *
     * @return \Magento\Framework\DataObject
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * Set item context
     *
     * @param \Doofinder\Feed\Model\Generator\Item[]
     * @return Item
     */
    public function setAssociates(array $items)
    {
        $this->_associates = $items;
        return $this;
    }

    /**
     * Get item associates
     *
     * @return \Doofinder\Feed\Model\Generator\Item[]
     */
    public function getAssociates()
    {
        return $this->_associates;
    }

    /**
     * Has item associates
     *
     * @return boolean
     */
    public function hasAssociates()
    {
        return !empty($this->_associates);
    }

    /**
     * Skip item
     *
     * @return \Doofinder\Feed\Model\Generator\Item
     */
    public function skip()
    {
        $this->_skip = true;
        return $this;
    }

    /**
     * Check if item should be skipped
     *
     * @return boolean
     */
    public function isSkip()
    {
        return $this->_skip;
    }

    /**
     * Check if array is associative
     *
     * @param array
     * @return boolean
     */
    private function isAssocArray(array $array)
    {
        $keys = array_keys($array);
        return $keys !== array_keys($keys);
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
