<?php

namespace Doofinder\Feed\Model\Config\Source\Feed;

/**
 * Attributes source
 */
class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Eav Model Config
     *
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    private $escaper;
    /**
     * Array with Doofinder Options and Product Attributes.
     *
     * @var array
     */
    private $options;

    /**
     * Attributes constructor.
     *
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->eavConfig = $eavConfig;
        $this->escaper = $escaper;
    }

    /**
     * Return array of options as value-label pairs, eg. attribute_code => attribute_label.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = array_merge(
                $this->_getDoofinderDirectivesOptionArray(),
                $this->_getCatalogProductAttributes()
            );
        }

        return $this->options;
    }

    /**
     * Getting all attributes into one array.
     *
     * @return array
     */
    public function getAllAttributes()
    {
        $doofinderOptions = $this->_getDoofinderDirectivesOptionArray();
        $productAttributes = $this->_getCatalogProductAttributes();

        foreach ($doofinderOptions as $code => $value) {
            $value = $this->escaper->escapeJsQuote(($value));
            $doofinderOptions[$code] = $value;
        }

        foreach ($productAttributes as $code => $value) {
            $value = $this->escaper->escapeJsQuote(($value));
            $productAttributes[$code] = $value;
        }

        return array_merge($doofinderOptions, $productAttributes);
    }

    /**
     * Getting all product attributes.
     *
     * @return array
     */
    private function _getCatalogProductAttributes()
    {
        $attributes = [];

        $productEntity = \Magento\Catalog\Model\Product::ENTITY;
        $collection = $this->eavConfig->getEntityType($productEntity)->getAttributeCollection();

        foreach ($collection as $attribute) {
            $code = $attribute->getAttributeCode();
            $label = $attribute->getFrontendLabel();

            $attributes[$code] = 'Attribute: ' . $code . ($label ? ' (' . $label . ')' : '');
        }

        return $attributes;
    }

    /**
     * Getting all doofinder options from directives in xml config.
     *
     * @return array
     */
    private function _getDoofinderDirectivesOptionArray()
    {
        return [
            'df_id' => __('Doofinder: Product Id'),
            'df_availability' => __('Doofinder: Product Availability'),
            'df_currency' => __('Doofinder: Product Currency'),
            'df_regular_price' => __('Doofinder: Product Regular Price'),
            'df_sale_price' => __('Doofinder: Product Sale Price'),
            'df_minimal_tier_price' => __('Doofinder: Product Minimal Tier Price'),
        ];
    }
}
