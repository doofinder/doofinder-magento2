<?php

namespace Doofinder\Feed\Model\Config\Source\Feed;

/**
 * Class Attributes
 *
 * @package Doofinder\Feed\Model\Config\Source\Feed
 */
class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Scope config interface.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * Eav Model Config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    /**
     * Array with Doofinder Options and Product Attributes.
     *
     * @var array
     */
    protected $_options;

    /**
     * Attributes constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_eavConfig = $eavConfig;
        $this->_escaper = $escaper;
    }

    /**
     * Return array of options as value-label pairs, eg. attribute_code => attribute_label.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array_merge(
                $this->_getDoofinderDirectivesOptionArray(),
                $this->_getCatalogProductAttributes());
        }

        return $this->_options;
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

        foreach($doofinderOptions as $code => $value) {
            $value = $this->_escaper->escapeJsQuote(($value));
            $doofinderOptions[$code] = $value;
        }

        foreach($productAttributes as $code => $value) {
            $value = $this->_escaper->escapeJsQuote(($value));
            $productAttributes[$code] = $value;
        }

        return array_merge($doofinderOptions, $productAttributes);
    }

    /**
     * Getting all product attributes.
     *
     * @return array
     */
    protected function _getCatalogProductAttributes()
    {
        $attributes = array();

        $productEntity = \Magento\Catalog\Model\Product::ENTITY;
        $collection = $this->_eavConfig->getEntityType($productEntity)->getAttributeCollection();

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
    protected function _getDoofinderDirectivesOptionArray()
    {
        $options = array();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $directives = $this->_scopeConfig->getValue('directives', $storeScope);

        foreach ($directives as $directive => $label) {
            $options[$directive] = 'Doofinder: ' . $label;
        }

        return $options;
    }
}
