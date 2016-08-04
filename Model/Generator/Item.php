<?php

namespace Doofinder\Feed\Model\Generator;

class Item extends \Magento\Framework\DataObject implements \Sabre\Xml\XmlSerializable
{
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
            $properties[] = [
                'name' => $key,
                'value' => is_array($value) ? $this->convertDataToXmlProperty($value) : $value,
            ];
        }

        return $properties;
    }
}
