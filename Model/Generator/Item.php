<?php

namespace Doofinder\Feed\Model\Generator;

/**
 * Item
 */
class Item extends \Magento\Framework\DataObject implements \Sabre\Xml\XmlSerializable
{
    /**
     * @var \Magento\Framework\DataObject|null
     */
    private $context = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item[]
     */
    private $associates = [];

    /**
     * @var boolean
     */
    private $skip = false;

    /**
     * Serialize item to an XML
     *
     * @param  \Sabre\Xml\Writer $writer
     * @return void
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
     * @param  array $data
     * @return array
     */
    private function convertDataToXmlProperty(array $data)
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

            if (!is_array($value) && !is_a($value, \Sabre\Xml\XmlSerializable::class)) {
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
     * @param  \Magento\Framework\DataObject $context
     * @return Item
     */
    public function setContext(\Magento\Framework\DataObject $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get item context
     *
     * @return \Magento\Framework\DataObject
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set item context
     *
     * @param  \Doofinder\Feed\Model\Generator\Item[] $items
     * @return Item
     */
    public function setAssociates(array $items)
    {
        $this->associates = $items;
        return $this;
    }

    /**
     * Get item associates
     *
     * @return \Doofinder\Feed\Model\Generator\Item[]
     */
    public function getAssociates()
    {
        return $this->associates;
    }

    /**
     * Has item associates
     *
     * @return boolean
     */
    public function hasAssociates()
    {
        return !empty($this->associates);
    }

    /**
     * Skip item
     *
     * @return \Doofinder\Feed\Model\Generator\Item
     */
    public function skip()
    {
        $this->skip = true;
        return $this;
    }

    /**
     * Check if item should be skipped
     *
     * @return boolean
     */
    public function isSkip()
    {
        return $this->skip;
    }

    /**
     * Check if array is associative
     *
     * @param  array $array
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
