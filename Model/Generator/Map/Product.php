<?php

namespace Doofinder\Feed\Model\Generator\Map;

use \Doofinder\Feed\Model\Generator\Map;
use \Doofinder\Feed\Model\Config\Source\Feed\PriceTaxMode;

/**
 * Product map
 */
class Product extends Map
{
    /**
     * @var \Doofinder\Feed\Helper\Product
     * @codingStandardsIgnoreStart
     */
    protected $helper = null;
    // @codingStandardsIgnoreEnd

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * Tax helper
     *
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * Class constructor
     *
     * @param \Doofinder\Feed\Helper\Product $helper
     * @param \Doofinder\Feed\Model\Generator\Item $item
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException Context is not a product.
     * @codingStandardsIgnoreStart
     * Ignore MEQP2.Classes.ConstructorOperations.CustomOperationsFound
     */
    public function __construct(
        \Doofinder\Feed\Helper\Product $helper,
        \Doofinder\Feed\Model\Generator\Item $item,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
    // @codingStandardsIgnoreEnd
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->taxConfig = $taxConfig;

        if (!is_a($item->getContext(), \Magento\Catalog\Model\Product::class)) {
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @codingStandardsIgnoreStart
     */
    public function get($field)
    {
    // @codingStandardsIgnoreEnd
        switch ($field) {
            case 'df_id':
                return $this->getProductId($this->context);

            case 'url_key':
                return $this->getProductUrl($this->context);

            case 'category_ids':
                return $this->getProductCategories($this->context, $this->getCategoriesInNavigation());

            case 'image':
                return $this->getProductImage($this->context, $this->getImageSize());

            case 'df_regular_price':
                return $this->getProductPrice($this->context, 'regular_price');

            case 'df_sale_price':
                $salePrice = $this->getProductPrice($this->context, 'final_price');

                if ($salePrice < $this->getProductPrice($this->context, 'regular_price')) {
                    // Only return 'sale price' if is less than 'regular price'
                    return $salePrice;
                }

                return null;

            case 'price':
            case 'special_price':
            case 'tier_price':
            case 'minimal_price':
                return $this->getProductPrice($this->context, $field);

            case 'df_availability':
                return $this->getProductAvailability($this->context);

            case 'df_currency':
                return $this->getCurrencyCode();

            case 'quantity_and_stock_status':
                return $this->getQuantityAndStockStatus($this->context);
        }

        return $this->getAttributeText($this->context, $field);
    }

    /**
     * Get product id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return integer
     */
    public function getProductId(\Magento\Catalog\Model\Product $product)
    {
        return $this->helper->getProductId($product);
    }

    /**
     * Get product url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        return $this->helper->getProductUrl($product);
    }

    /**
     * Get product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param boolean $categoriesInNav Export only categories in navigation.
     * @return string
     */
    public function getProductCategories(\Magento\Catalog\Model\Product $product, $categoriesInNav)
    {
        $tree = $this->helper->getProductCategoriesWithParents($product, $categoriesInNav);

        /**
         * Return array with stringified category tree
         * example: ['Category 1>Category 1.1', 'Category 2 > Category 2.1']
         */
        return array_map(function ($categories) {
            return implode(
                \Doofinder\Feed\Model\Generator::CATEGORY_TREE_SEPARATOR,
                array_map(function ($category) {
                    return $category->getName();
                }, $categories)
            );
        }, $tree);
    }

    /**
     * Get product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $size
     * @return string|null
     */
    public function getProductImage(\Magento\Catalog\Model\Product $product, $size)
    {
        return $this->helper->getProductImageUrl($product, $size);
    }

    /**
     * Get product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @return string|null
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product, $field)
    {
        if (!$this->getExportProductPrices()) {
            return null;
        }

        $tax = null;
        if ($this->taxConfig->needPriceConversion()) {
            switch ($this->getPriceTaxMode()) {
                case PriceTaxMode::MODE_WITH_TAX:
                    $tax = true;
                    break;

                case PriceTaxMode::MODE_WITHOUT_TAX:
                    $tax = false;
                    break;
            }
        }

        // Return price converted to store currency
        return $this->helper->getProductPrice($product, $field, $tax);
    }

    /**
     * Get product availability
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductAvailability(\Magento\Catalog\Model\Product $product)
    {
        return $this->helper->getProductAvailability($product);
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->helper->getCurrencyCode();
    }

    /**
     * Get quantity and stock status
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getQuantityAndStockStatus(\Magento\Catalog\Model\Product $product)
    {
        return $this->helper->getQuantityAndStockStatus($product);
    }

    /**
     * Get attribute text
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @return string
     */
    public function getAttributeText(\Magento\Catalog\Model\Product $product, $field)
    {
        return $this->helper->getAttributeText($product, $field);
    }
}
