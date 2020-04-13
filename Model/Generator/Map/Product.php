<?php

namespace Doofinder\Feed\Model\Generator\Map;

use Doofinder\Feed\Model\Config\Source\Feed\PriceTaxMode;
use Doofinder\Feed\Model\Generator\MapInterface;

/**
 * Product map
 */
class Product implements MapInterface
{
    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    private $productHelper;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * Product constructor.
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\Product $helper
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\Product $helper,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->storeConfig = $storeConfig;
        $this->productHelper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->taxConfig = $taxConfig;
    }

    /**
     * {@inheritDoc}
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     */
    public function get(\Magento\Catalog\Model\Product $product, $field)
    {
        if (!$field) {
            return '';
        }

        switch ($field) {
            case 'df_id':
                return $this->getProductId($product);

            case 'url_key':
                return $this->getProductUrl($product);

            case 'category_ids':
                return $this->getProductCategories(
                    $product,
                    $this->isExportCategoriesInNavigation($product->getStoreId())
                );

            case 'image':
            case 'small_image':
            case 'thumbnail':
                return $this->getProductImage(
                    $product,
                    $this->getImageSize($product->getStoreId()),
                    $field
                );

            case 'df_regular_price':
                return $this->getProductPrice($product, 'regular_price');

            case 'df_sale_price':
                $salePrice = $this->getProductPrice($product, 'final_price');

                if ($salePrice < $this->getProductPrice($product, 'regular_price')) {
                    // Only return 'sale price' if is less than 'regular price'
                    return $salePrice;
                }

                return null;

            case 'price':
            case 'special_price':
            case 'tier_price':
            case 'minimal_price':
            case 'final_price':
            case 'regular_price':
                return $this->getProductPrice($product, $field);

            case 'df_availability':
                return $this->getProductAvailability($product);

            case 'df_currency':
                return $this->getCurrencyCode();

            case 'quantity_and_stock_status':
                return $this->getQuantityAndStockStatus($product);
        }

        return $this->getAttributeText($product, $field);
        // phpcs:enable
    }

    /**
     * Get product id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return integer
     */
    public function getProductId(\Magento\Catalog\Model\Product $product)
    {
        return $this->productHelper->getProductId($product);
    }

    /**
     * Get product url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        return $this->productHelper->getProductUrl($product);
    }

    /**
     * Get product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param boolean $categoriesInNav Export only categories in navigation.
     * @return array
     */
    public function getProductCategories(\Magento\Catalog\Model\Product $product, $categoriesInNav)
    {
        $tree = $this->productHelper->getProductCategoriesWithParents($product, $categoriesInNav);

        /**
         * Return array with stringified category tree
         * example: ['Category 1>Category 1.1', 'Category 2 > Category 2.1']
         */
        return array_filter(
            array_values(
                array_map(function ($categories) {
                    return implode(
                        '>',
                        array_map(function ($category) {
                            return $category->getName();
                        }, $categories)
                    );
                }, $tree)
            )
        );
    }

    /**
     * Get product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $size
     * @param string $field
     * @return string|null
     */
    public function getProductImage(\Magento\Catalog\Model\Product $product, $size, $field)
    {
        return $this->productHelper->getProductImageUrl($product, $size, $field);
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
        if (!$this->isExportProductPrices($product->getStoreId())) {
            return null;
        }

        $tax = null;
        if ($this->taxConfig->needPriceConversion()) {
            switch ($this->getPriceTaxMode($product->getStoreId())) {
                case PriceTaxMode::MODE_WITH_TAX:
                    $tax = true;
                    break;

                case PriceTaxMode::MODE_WITHOUT_TAX:
                    $tax = false;
                    break;
            }
        }

        // Return price converted to store currency
        return $this->productHelper->getProductPrice($product, $field, $tax);
    }

    /**
     * Get product availability
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductAvailability(\Magento\Catalog\Model\Product $product)
    {
        return $this->productHelper->getProductAvailability($product);
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->productHelper->getCurrencyCode();
    }

    /**
     * Get quantity and stock status
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getQuantityAndStockStatus(\Magento\Catalog\Model\Product $product)
    {
        return $this->productHelper->getQuantityAndStockStatus($product);
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
        return $this->productHelper->getAttributeText($product, $field);
    }

    /**
     * @param integer|null $storeId
     * @return boolean
     */
    public function isExportCategoriesInNavigation($storeId = null)
    {
        return $this->storeConfig->isExportCategoriesInNavigation($storeId);
    }

    /**
     * @param integer|null $storeId
     * @return string
     */
    public function getImageSize($storeId = null)
    {
        return $this->storeConfig->getImageSize($storeId);
    }

    /**
     * @param integer|null $storeId
     * @return boolean
     */
    public function isExportProductPrices($storeId = null)
    {
        return $this->storeConfig->isExportProductPrices($storeId);
    }

    /**
     * @param integer|null $storeId
     * @return string
     */
    public function getPriceTaxMode($storeId = null)
    {
        return $this->storeConfig->getPriceTaxMode($storeId);
    }

    /**
     * @return \Doofinder\Feed\Helper\Product
     */
    public function getProductHelper()
    {
        return $this->productHelper;
    }

    /**
     * @return \Doofinder\Feed\Helper\StoreConfig
     */
    public function getStoreConfigHelper()
    {
        return $this->storeConfig;
    }
}
