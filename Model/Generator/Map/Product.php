<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Generator\Map;

use Doofinder\Feed\Api\Data\Generator\MapInterface;
use Doofinder\Feed\Helper\Product as ProductHelper;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ProductTypeConfigurable;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Config as TaxConfig;

class Product implements MapInterface
{
    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var ProductTypeConfigurable
     */
    private $productTypeConfigurable;

    /**
     * @var ProductOption
     */
    private $productOption;


    /**
     * Product constructor.
     *
     * @param ProductHelper $helper
     * @param StoreConfig $storeConfig
     * @param TaxConfig $taxConfig
     * @param ProductTypeConfigurable $productTypeConfigurable
     * @param ProductOption $productOption
     */
    public function __construct(
        ProductHelper $helper,
        StoreConfig $storeConfig,
        TaxConfig $taxConfig,
        ProductTypeConfigurable $productTypeConfigurable,
        ProductOption $productOption
    ) {
        $this->productHelper = $helper;
        $this->storeConfig = $storeConfig;
        $this->taxConfig = $taxConfig;
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->productOption = $productOption;
    }

    /**
     * @param ProductModel $product
     * @param string $field
     *
     * @return mixed|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function get(ProductModel $product, string $field)
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

            case 'image_link':
                return $this->getProductImageLink($product);

            case 'df_grouping_id':
                return $this->getGroupingId($product);
            case 'df_group_leader':
                return $this->isGroupLeader($product);
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

            case 'options':
                return $this->getProductOptions($product);
        }

        if ("array" === $this->getAttributeType($product, $field)) {
            return $this->getAttributeArray($product, $field);
        }

        return $this->getAttributeText($product, $field);
        // phpcs:enable
    }

    /**
     * Get product id
     *
     * @param ProductModel $product
     *
     * @return string
     */
    public function getProductId(ProductModel $product): string
    {
        return (string)$this->productHelper->getProductId($product);
    }

    /**
     * Get product url
     *
     * @param ProductModel $product
     *
     * @return string
     */
    public function getProductUrl(ProductModel $product): string
    {
        return $this->productHelper->getProductUrl($product);
    }

    /**
     * Get product categories
     *
     * @param ProductModel $product
     * @param boolean $categoriesInNav Export only categories in navigation.
     *
     * @return array
     * @throws LocalizedException
     */
    public function getProductCategories(ProductModel $product, bool $categoriesInNav): array
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
     * Get the product options in an object format
     *
     * @param ProductModel $product
     *
     * @return object
     */
    public function getProductOptions(ProductModel $product)
    {
        $options = [];
        $productOptions = $this->productOption->getProductOptionCollection($product);
        foreach ($productOptions->getItems() as $item) {
            $tmp = [
                "product_sku" => $item["sku"],
                "option_id" => (int)$item["option_id"],
                "title" => $item["title"],
                "type" => $item["type"],
                "sort_order" => (int)$item["sort_order"],
                "is_require" => (bool)$item["is_require"],
                "price" => (float)$item["price"],
                "price_type" => $item["price_type"],
                "max_characters" => (int)$item["max_characters"],
                "image_size_x" => (int)$item["image_size_x"],
                "image_size_y" => (int)$item["image_size_y"]
            ];

            $options[] = (object)$tmp;
        }
        return $options;
    }
    /**
     * Get product image
     *
     * @param ProductModel $product
     * @param string $size
     * @param string $field
     *
     * @return string|null
     */
    public function getProductImage(ProductModel $product, string $size, string $field): ?string
    {
        return $this->productHelper->getProductImageUrl($product, $size, $field);
    }

    /**
     * Get the product image URL
     *
     * @param ProductModel $product
     *
     * @return boolean
     */
    public function getProductImageLink(ProductModel $product): string
    {
        if ($product->getImage()) {
            $baseUrl = $this->storeConfig->getCurrentStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $baseUrl . 'catalog/product' . $product->getImage();
        }
        return '';
    }

    /**
     * Get df_grouping_id value
     *
     * @param ProductModel $product
     *
     * @return int|null
     */
    public function getGroupingId(ProductModel $product): ?string
    {
        $parentIds = $this->productTypeConfigurable->getParentIdsByChild($product->getId());

        return count($parentIds) ? $parentIds[0] : $product->getId();
    }

    /**
     * Check if the given product is group leader or not
     *
     * @param ProductModel $product
     *
     * @return boolean
     */
    public function isGroupLeader(productModel $product): bool
    {
        return ($product->getTypeId() == ProductType::TYPE_SIMPLE && count($this->productTypeConfigurable->getParentIdsByChild($product->getId())) == 0);
    }

    /**
     * Get product price
     *
     * @param ProductModel $product
     * @param string $field
     *
     * @return float|null
     */
    public function getProductPrice(ProductModel $product, string $field): ?float
    {
        if (!$this->isExportProductPrices((int)$product->getStoreId())) {
            return null;
        }
        $tax = null;
        if ($this->taxConfig->needPriceConversion()) {
            switch ($this->getPriceTaxMode((int)$product->getStoreId())) {
                case TaxConfig::DISPLAY_TYPE_INCLUDING_TAX:
                    $tax = true;
                    break;

                case TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX:
                    $tax = false;
                    break;
            }
        }

        // Return price converted to store currency
        return $this->productHelper->getProductPrice($product, $field, $tax);
    }

    /**
     * Get currency code
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrencyCode(): string
    {
        return $this->productHelper->getCurrencyCode();
    }

    /**
     * Get quantity and stock status
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return string
     * @throws LocalizedException
     */
    public function getQuantityAndStockStatus(ProductModel $product, ?int $stockId = null): string
    {
        return $this->productHelper->getQuantityAndStockStatus($product, $stockId);
    }

    /**
     * Get product availability
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return string
     * @throws LocalizedException
     */
    public function getProductAvailability(ProductModel $product, ?int $stockId = null): string
    {
        return $this->productHelper->getProductAvailability($product, $stockId);
    }

    /**
     * Get attribute type
     *
     * @param ProductModel $product
     * @param string $attributeCode
     *
     * @return string|null
     */
    public function getAttributeType(ProductModel $product, string $attributeCode): ?string
    {
        return $this->productHelper->getAttributeType($product, $attributeCode);
    }

    /**
     * Get attribute text
     *
     * @param ProductModel $product
     * @param string $attributeCode
     *
     * @return string|null
     */
    public function getAttributeText(ProductModel $product, string $attributeCode): ?string
    {
        return $this->productHelper->getAttributeText($product, $attributeCode);
    }

    /**
     * Get attribute as array
     *
     * @param ProductModel $product
     * @param string $attributeCode
     *
     * @return array|null
     */
    public function getAttributeArray(ProductModel $product, string $attributeCode): ?array
    {
        return $this->productHelper->getAttributeArray($product, $attributeCode);
    }


    /**
     * @param integer|null $storeId
     * @return boolean
     */
    public function isExportCategoriesInNavigation(?int $storeId = null): bool
    {
        return $this->storeConfig->isExportCategoriesInNavigation($storeId);
    }

    /**
     * @param integer|null $storeId
     * @return string|null
     */
    public function getImageSize(?int $storeId = null): ?string
    {
        return $this->storeConfig->getImageSize($storeId);
    }

    /**
     * @param integer|null $storeId
     * @return boolean
     */
    public function isExportProductPrices(?int $storeId = null): bool
    {
        return $this->storeConfig->isExportProductPrices($storeId);
    }

    /**
     * @param integer|null $storeId
     * @return string
     */
    public function getPriceTaxMode(?int $storeId = null): string
    {
        return $this->storeConfig->getPriceTaxMode($storeId);
    }
}
