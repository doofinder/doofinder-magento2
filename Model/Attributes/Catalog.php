<?php

namespace Doofinder\Feed\Model\Attributes;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;

/**
 * Class Catalog
 * The class responsible for providing product attributes
 */
class Catalog implements ArrayInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var array|null
     */
    private $attributes;

    /**
     * Catalog constructor.
     * @param Config $eavConfig
     */
    public function __construct(Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->attributes) {
            $this->attributes = [];
            $collection = $this->eavConfig->getEntityType(Product::ENTITY)->getAttributeCollection();

            foreach ($collection as $attribute) {
                $code = $attribute->getAttributeCode();
                $label = $attribute->getFrontendLabel();

                $this->attributes[$code] = $this->getLabel($code, $label);
            }
        }
        return $this->attributes;
    }

    /**
     * @param string $code
     * @param string|null $label
     * @return string
     */
    private function getLabel($code, $label = null)
    {
        $label = $label ?? '';
        return __(
            'Attribute: %1 (%2)',
            $code,
            $label
        );
    }
}
