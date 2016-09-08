<?php

namespace Doofinder\Feed\Model\Generator;

class Item extends \Magento\Framework\DataObject implements \Sabre\Xml\XmlSerializable
{
    /**
     * @var \Magento\Framework\DataObject|null
     */
    protected $_context = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item[]
     */
    protected $_associates = [];

    /**
     * @var boolean
     */
    protected $_skip = false;

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
    protected function convertDataToXmlProperty($data)
    {
        $properties = [];

        foreach ($data as $key => $value) {
            if (!$value) {
                continue;
            } else if (is_array($value)) {
                $value = $this->isAssocArray($value) ? $this->convertDataToXmlProperty($value) : implode(',', $value);
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
    protected function isAssocArray(array $array)
    {
        $keys = array_keys($array);
        return $keys !== array_keys($keys);
    }
}
