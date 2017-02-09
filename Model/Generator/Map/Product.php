<?php

namespace Doofinder\Feed\Model\Generator\Map;

use \Doofinder\Feed\Model\Generator\Map;

/**
 * Class Product
 *
 * @package Doofinder\Feed\Model\Generator\Map
 */
class Product extends Map
{
    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    protected $_helper = null;

    /**
     * Class constructor
     *
     * @param \Doofinder\Feed\Helper\Product $helper
     * @param \Doofinder\Feed\Model\Generator\Item $item
     * @param array $data = []
     */
    public function __construct(
        \Doofinder\Feed\Helper\Product $helper,
        \Doofinder\Feed\Model\Generator\Item $item,
        array $data = []
    ) {
        $this->_helper = $helper;

        if (!is_a($item->getContext(), '\Magento\Catalog\Model\Product')) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Item context is not a product')
            );
        }

        parent::__construct($item, $data);
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
                return $this->getProductCategories($this->_context, $this->getCategoriesInNavigation());

            case 'image':
                return $this->getProductImage($this->_context, $this->getImageSize());

            case 'df_regular_price':
                return $this->getProductPrice($this->_context, 'regular_price');

            case 'df_sale_price':
                return $this->getProductPrice($this->_context, 'final_price');

            case 'price':
            case 'special_price':
            case 'tier_price':
            case 'minimal_price':
                return $this->getProductPrice($this->_context, $field);

            case 'df_availability':
                return $this->getProductAvailability($this->_context);

            case 'df_currency':
                return $this->getCurrencyCode();

            case 'quantity_and_stock_status':
                return $this->getQuantityAndStockStatus($this->_context);
        }

        return $this->getAttributeText($this->_context, $field);
    }

    /**
     * Get product id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    protected function getProductId(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductId($product);
    }

    /**
     * Get product url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductUrl($product);
    }

    /**
     * Get product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param boolean $categoriesInNavigation - Export only categories in navigation
     * @return string
     */
    protected function getProductCategories(\Magento\Catalog\Model\Product $product, $categoriesInNavigation)
    {
        $tree = $this->_helper->getProductCategoriesWithParents($product, $categoriesInNavigation);

        /**
         * Stringifies tree by imploding a set of imploded categories and their parents
         * example: Category 1 > Category 1.1 % Category 2 > Category 2.1 > Category 2.1.1
         */
        return implode(
            \Doofinder\Feed\Model\Generator::CATEGORY_SEPARATOR,
            array_map(function ($categories) {
                return implode(
                    \Doofinder\Feed\Model\Generator::CATEGORY_TREE_SEPARATOR,
                    array_map(function ($category) {
                        return $category->getName();
                    }, $categories)
                );
            }, $tree)
        );
    }

    /**
     * Get product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $size
     * @return string|null
     */
    protected function getProductImage(\Magento\Catalog\Model\Product $product, $size)
    {
        return $this->_helper->getProductImageUrl($product, $size);
    }

    /**
     * Get product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @return string|null
     */
    protected function getProductPrice(\Magento\Catalog\Model\Product $product, $field)
    {
        if (!$this->getExportProductPrices()) {
            return null;
        }

        $price = $this->_helper->getProductPrice($product, $field);

        return number_format($price, 2, '.', '');
    }

    /**
     * Get product availability
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getProductAvailability(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductAvailability($product);
    }

    /**
     * Get currency code
     *
     * @return string
     */
    protected function getCurrencyCode()
    {
        return $this->_helper->getCurrencyCode();
    }

    /**
     * Get quantity and stock status
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getQuantityAndStockStatus(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getQuantityAndStockStatus($product);
    }

    /**
     * Get attribute text
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @return string
     */
    protected function getAttributeText(\Magento\Catalog\Model\Product $product, $field)
    {
        return $this->_helper->getAttributeText($product, $field);
    }
}
