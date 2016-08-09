<?php

namespace Doofinder\Feed\Model\Generator;

class Item extends \Magento\Framework\DataObject implements \Sabre\Xml\XmlSerializable
{
    /**
     * @var \Magento\Framework\DataObject|null
     */
    protected $_context = null;

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
