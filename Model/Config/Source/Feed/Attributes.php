<?php

namespace Doofinder\Feed\Model\Config\Source\Feed;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    protected $_scopeConfig;
    protected $_eavConfig;
    protected $_options;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_eavConfig = $eavConfig;
    }

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array_merge(
                $this->_getDoofinderDirectivesOptionArray(),
                $this->_getCatalogProductAttributes());
        }

        return $this->_options;
    }

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
