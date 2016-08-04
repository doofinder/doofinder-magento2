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
            'item' => $this->getData()
        ]);
    }
}
