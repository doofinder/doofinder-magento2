<?php

namespace Doofinder\Feed\Helper;

use Magento\Framework\UrlInterface;

/**
 * Product helper
 */
class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryColFactory = null;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * Static cache for category tree
     * @var \Magento\Catalog\Model\Category[][]
     */
    private $categoryTree;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Tax\Model\Config $taxConfig
     * @codingStandardsIgnoreStart
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        // * @codingStandardsIgnoreEnd
        $this->categoryColFactory = $categoryColFactory;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->categoryTree = [];
        $this->taxConfig = $taxConfig;
        parent::__construct($context);
    }

    /**
     * Get product id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return integer
     */
    public function getProductId(\Magento\Catalog\Model\Product $product)
    {
        return $product->getId();
    }

    /**
     * Get product url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        return $product->getUrlInStore(['_type' => UrlInterface::URL_TYPE_WEB]);
    }

    /**
     * Get categories
     *
     * @param int[] $ids
     * @return \Magento\Catalog\Model\Category[]
     */
    private function getCategories(array $ids)
    {
        $categoryCollection = $this->categoryColFactory->create();
        $categoryCollection
            ->addIdFilter($ids)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('include_in_menu')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('level', ['gt' => 1]);

        return $categoryCollection->getItems();
    }

    /**
     * Get category tree
     *
     * @param \Magento\Catalog\Model\Category[] $categories
     * @param boolean $fromNavigation Exclude categories not in menu.
     * @return \Magento\Catalog\Model\Category[][]
     */
    private function getCategoryTree(array $categories, $fromNavigation)
    {
        // Store all requested category ids
        $categoryIds = array_map(function ($category) {
            return $category->getId();
        }, $categories);

        // Exclude previously processed categories
        $categories = array_diff_key($categories, $this->categoryTree);

        // Grab ids of all parent categories of all product categories
        $parentIds = [];
        array_walk($categories, function ($category) use (&$parentIds) {
            $parentIds = array_merge($parentIds, $category->getParentIds());
        });
        $parentIds = array_unique($parentIds);

        // Combine product categories with its parents for simplicity
        $parents = $categories + $this->getCategories($parentIds);

        // Now build tree of categories with its parents
        foreach ($categories as $category) {
            $categoryId = $parentId = $category->getId();

            while ($parentId) {
                // Ignore category if one of parents is missing
                // this means that parent is not active
                if (!isset($parents[$parentId])) {
                    $this->categoryTree[$categoryId] = [];
                    break;
                }

                // Do not process categories not in menu if $fromNavigation is set
                if ($fromNavigation && !$parents[$parentId]->getIncludeInMenu()) {
                    break;
                }

                $this->categoryTree[$categoryId][$parentId] = $parents[$parentId];

                // Stop processing on 2nd level
                if ($parents[$parentId]->getLevel() <= 2) {
                    break;
                }

                $parentId = $parents[$parentId]->getParentId();
            }

            // Now reverse the order to make parents before children
            if (!empty($this->categoryTree[$categoryId])) {
                $this->categoryTree[$categoryId] = array_reverse($this->categoryTree[$categoryId], true);
            }
        }

        // Return tree
        return array_intersect_key($this->categoryTree, array_flip($categoryIds));
    }

    /**
     * Get product categories tree
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param boolean $fromNavigation Exclude categories not in menu.
     * @return \Magento\Catalog\Model\Category[][]
     */
    public function getProductCategoriesWithParents(
        \Magento\Catalog\Model\Product $product,
        $fromNavigation = false
    ) {
        $categories = $this->getCategories($product->getCategoryIds());
        return $this->getCategoryTree($categories, $fromNavigation);
    }

    /**
     * Get product image url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $size
     * @return string|null
     */
    public function getProductImageUrl(\Magento\Catalog\Model\Product $product, $size = null)
    {
        if ($product->hasImage()) {
            return $this->imageHelper
                ->init($product, 'doofinder_image')
                ->resize($size)
                ->getUrl();
        }
    }

    /**
     * Get product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attribute
     * @param boolean|null $tax
     * @return float
     */
    public function getProductPrice(
        \Magento\Catalog\Model\Product $product,
        $attribute = 'price',
        $tax = null
    ) {
        switch ($attribute) {
            case 'special_price':
            case 'tier_price':
            case 'regular_price':
                $type = $attribute;
                break;

            default:
                $type = 'final_price';
        }

        $price = $product->getPriceInfo()->getPrice($type);
        $amount = $price->getAmount();

        if ($tax === null) {
            $taxConfig = $this->taxConfig;
            $tax = $this->taxConfig->getPriceDisplayType() != $taxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
        }

        if (!$tax) {
            // No tax needed, use base amount
            $value = $amount->getBaseAmount();
        } elseif ($this->taxConfig->priceIncludesTax()) {
            // Tax already included, use value
            $value = $amount->getValue();
        } else {
            // Tax needed but not included in base price, apply tax
            // Apply tax to base amount to make sure tax is not added twice
            $adjustment = $product->getPriceInfo()->getAdjustment('tax');
            $value = $adjustment->applyAdjustment($amount->getBaseAmount(), $product);
        }

        return $value;
    }

    /**
     * Get product availability
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductAvailability(\Magento\Catalog\Model\Product $product)
    {
        if ($this->getStockItem($product->getId())->getIsInStock()) {
            return $this->getInStockLabel();
        }

        return $this->getOutOfStockLabel();
    }

    /**
     * Get product 'out of stock' label
     *
     * @return string
     */
    public function getOutOfStockLabel()
    {
        return 'out of stock';
    }

    /**
     * Get product 'in stock' label
     *
     * @return string
     */
    public function getInStockLabel()
    {
        return 'in stock';
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get attribute text
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeName
     * @return string
     */
    public function getAttributeText(\Magento\Catalog\Model\Product $product, $attributeName)
    {
        $frontend = $product->getResource()->getAttribute($attributeName)->getFrontend();
        $value = $product->getData($attributeName);

        if (!$value) {
            return null;
        }

        $value = $frontend->getOption($value);

        if (!$value) {
            $value = $frontend->getValue($product);
        }

        if (is_a($value, \Magento\Framework\Phrase::class)) {
            $value = $value->render();
        }

        return $value;
    }

    /**
     * Get quantity and stock status
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getQuantityAndStockStatus(\Magento\Catalog\Model\Product $product)
    {
        $qty = $this->getStockItem($product->getId())->getQty();
        $availability = $this->getProductAvailability($product);

        return implode(' - ', array_filter([$qty, $availability], function ($item) {
            return $item !== null;
        }));
    }

    /**
     * Get stock item
     *
     * @param integer $productId
     * @return \Magento\CatalogInventory\Model\Stock\Item
     */
    private function getStockItem($productId)
    {
        return $this->stockRegistry->getStockItem($productId);
    }
}
