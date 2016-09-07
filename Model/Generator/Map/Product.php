<?php

namespace Doofinder\Feed\Model\Generator\Map;

use \Doofinder\Feed\Model\Generator\Map;

class Product extends Map
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_context;

    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    protected $_helper = null;

    /**
     * Class constructor
     *
     * @param \Doofinder\Feed\Helper\Product $helper
     * @param \Magento\Catalog\Model\Product $context
     * @param array $data = []
     */
    public function __construct(
        \Doofinder\Feed\Helper\Product $helper,
        \Magento\Catalog\Model\Product $context,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get value
     *
     * @param string $field
     * @return mixed
     */
    public function get($field)
    {
        switch ($field) {
            case 'df_id':
                return $this->getProductId($this->_context);

            case 'url_key':
                return $this->getProductUrl($this->_context);

            case 'category_ids':
                return $this->getProductCategories($this->_context);

            case 'image':
                return $this->getProductImage($this->_context, $this->getImageSize());

            case 'price':
                return $this->getProductPrice($this->_context);

            case 'availability':
                return $this->getProductAvailability($this->_context);

            case 'currency':
                return $this->getCurrencyCode();

            case 'quantity_and_stock_status':
                return $this->getQuantityAndStockStatus($this->_context);

            case 'color':
            case 'tax_class_id':
            case 'manufacturer':
            case 'weight_type':
                return $this->getAttributeText($this->_context, $field);
        }

        return parent::get($field);
    }

    /**
     * Get product id
     *
     * @param \Magento\Catalog\Model\Product
     * @return int
     */
    protected function getProductId(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductId($product);
    }

    /**
     * Get product url
     *
     * @param \Magento\Catalog\Model\Product
     * @return string
     */
    protected function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductUrl($product);
    }

    /**
     * Get product categories
     *
     * @todo This might need some optimalization
     *
     * @param \Magento\Catalog\Model\Product
     * @return string
     */
    protected function getProductCategories(\Magento\Catalog\Model\Product $product)
    {
        $categories = $this->_helper->getProductCategoriesWithParents($product);

        $entries = [];
        foreach ($categories as $entry) {
            $names = [];

            foreach ($entry as $category) {
                $names[] = $category->getName();
            }

            $entries[] = implode(' > ', $names);
        }

        return implode(' %% ', $entries);
    }

    /**
     * Get product image
     *
     * @todo Use store config
     *
     * @param \Magento\Catalog\Model\Product
     * @param string
     * @return string|null
     */
    protected function getProductImage(\Magento\Catalog\Model\Product $product, $imageSize)
    {
        return $this->_helper->getProductImageUrl($product, $imageSize);
    }

    /**
     * Get product price
     *
     * @todo Include minimal_price, split_configurable_products and export_product_prices params
     *
     * @param \Magento\Catalog\Model\Product
     * @return string|null
     */
    protected function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return number_format($this->_helper->getProductPrice($product), 2, '.', '');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    protected function getProductAvailability(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductAvailability($product);
    }

    /**
     * @return mixed
     */
    protected function getCurrencyCode()
    {
        return $this->_helper->getCurrencyCode();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getQuantityAndStockStatus(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getQuantityAndStockStatus($product);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @return string
     */
    protected function getAttributeText(\Magento\Catalog\Model\Product $product, $field)
    {
        return $this->_helper->getAttributeText($product, $field);
    }
}
