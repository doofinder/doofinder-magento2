<?php

declare(strict_types=1);

namespace Doofinder\Feed\Helper;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\Store\Model\Store as StoreModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Model\Product\Type as ProductType;

/**
 * Product helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends AbstractHelper
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var Url
     */
    private $frontendUrl;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ImageHelper $imageHelper
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param GetStockItemDataInterface $getStockItemData
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param TaxConfig $taxConfig
     * @param Url $frontendUrl
     * @param UrlFinderInterface $urlFinder
     * @param EavConfig $eavConfig
     * @param Configurable $configurable
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        ImageHelper $imageHelper,
        Context $context,
        StoreManagerInterface $storeManager,
        GetStockItemDataInterface $getStockItemData,
        DefaultStockProviderInterface $defaultStockProvider,
        TaxConfig $taxConfig,
        Url $frontendUrl,
        UrlFinderInterface $urlFinder,
        EavConfig $eavConfig,
        Configurable $configurable
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->getStockItemData = $getStockItemData;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->taxConfig = $taxConfig;
        $this->frontendUrl = $frontendUrl;
        $this->urlFinder = $urlFinder;
        $this->eavConfig = $eavConfig;
        $this->configurable = $configurable;
        parent::__construct($context);
    }

    /**
     * Get product id
     *
     * @param ProductModel $product
     *
     * @return integer
     */
    public function getProductId(ProductModel $product): int
    {
        return (int)$product->getId();
    }

    /**
     * Get product url
     *
     * This method is based on the \Magento\Catalog\Model\Product\Url::getUrl() method.
     *
     * @param ProductModel $product
     *
     * @return string
     */
    public function getProductUrl(ProductModel $product): string
    {
        $storeId = $product->getStoreId();
        $routePath = '';
        $requestPath = $product->getRequestPath();
        $filterData = [
            UrlRewrite::ENTITY_ID => $product->getId(),
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $storeId,
        ];
        $rewrite = $this->urlFinder->findOneByData($filterData);
        if ($rewrite) {
            $requestPath = $rewrite->getRequestPath();
        }
        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            // In case the product is a variant we need the ID of the configurable
            if (
                $product->getTypeId() == ProductType::TYPE_SIMPLE
                && count($parents = $this->configurable->getParentIdsByChild($product->getId())) > 0
            ) {
                $routeParams['id'] = $parents[0];
                $routeParams['s'] = $product->getUrlKey();
            } else {
                $routeParams['id'] = $product->getId();
                $routeParams['s'] = $product->getUrlKey();
            }
        }
        $routeParams['_scope'] = $storeId;
        $routeParams['_nosid'] = true;
        $routeParams['_type'] = UrlInterface::URL_TYPE_LINK;
        // Special mark that URL is building by Doofinder:
        $routeParams['doofinder_product_url'] = true;
        if ($this->scopeConfig->getValue(StoreModel::XML_PATH_STORE_IN_URL) == 1) {
            $routeParams['_scope_to_url'] = true;
        }

        return $this->frontendUrl->setScope($storeId)->getUrl(
            $routePath,
            $routeParams
        );
    }

    /**
     * Get categories
     *
     * @param int[] $ids
     * @param boolean $fromNavigation
     *
     * @return CategoryModel[]
     * @throws LocalizedException
     */
    private function getCategories(array $ids, ?bool $fromNavigation = false): array
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection
            ->addIdFilter($ids)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('include_in_menu')
            ->addAttributeToSelect('path')
            ->addAttributeToFilter('is_active', 1)
            ->addFieldToFilter('level', ['gt' => 1]);
        if ($fromNavigation) {
            $categoryCollection->addFieldToFilter('include_in_menu', $fromNavigation);
        }

        return $categoryCollection->getItems();
    }

    /**
     * Get category tree
     *
     * @param CategoryModel[] $categories
     * @param int[] $productCategoryIds
     * @param boolean $fromNavigation
     *
     * @return CategoryModel[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws LocalizedException
     */
    private function getCategoryTree(array $categories, array $productCategoryIds, ?bool $fromNavigation = false): array
    {
        // Store all category paths
        $catTree = [];
        foreach ($categories as $category) {
            $catTree[] = $category->getPath();
        }

        $catTree = $this->filterCategories($catTree, $fromNavigation);

        // Find same trees and store the deepest one
        $toRemove = [];
        foreach ($catTree as $item) {
            foreach ($catTree as $cat) {
                // Check if current path is a part of another path
                // if it is, mark path to remove from tree
                if (strstr($cat, $item . '/') !== false) {
                    $toRemove[$item] = true;
                    break;
                }
            }
        }

        // Get only needed category to build a tree
        $result = [];
        foreach ($categories as $category) {
            if (
                !isset($toRemove[$category->getPath()])
                && in_array($category->getPath(), $catTree)
            ) {
                $result[] = $category;
            }
        }

        // Build the tree
        $tree = [];
        foreach ($result as $category) {
            $ids = explode('/', $category->getPath());
            foreach ($ids as $key => $id) {
                if (!in_array($id, $productCategoryIds)) {
                    unset($ids[$key]);
                }
            }
            $tree[] = array_values(
                $this->getCategories(
                    $ids,
                    $fromNavigation
                )
            );
        }

        return array_filter($tree);
    }

    /**
     * Remove inactive or excluded from navigation trees
     *
     * @param array $catTree
     * @param boolean $fromNavigation
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function filterCategories(array $catTree, ?bool $fromNavigation = false): array
    {
        foreach ($catTree as $key => $item) {
            $tree = explode('/', $item);
            $activeTree = [];
            $categoryCollection = $this->categoryCollectionFactory->create();
            $categoryCollection
                ->addIdFilter($tree)
                ->addFieldToSelect('is_active')
                ->addFieldToSelect('include_in_menu')
                ->addFieldToFilter('level', ['gteq' => 1])
                ->addAttributeToSort('path');

            // check all categories in tree
            foreach ($categoryCollection->getItems() as $category) {
                /** @var CategoryModel $category */
                if (!$category->getIsActive()) {
                    break;
                }
                if ($fromNavigation && !$category->getIncludeInMenu()) {
                    break;
                }
                $activeTree[] = $category->getId();
            }
            array_unshift($activeTree, 1); // add id 1 as the main root category
            $catTree[$key] = implode('/', $activeTree);
        }

        return $catTree;
    }

    /**
     * Get product categories tree
     *
     * @param ProductModel $product
     * @param boolean $fromNavigation Exclude categories not in menu.
     *
     * @return CategoryModel[]
     * @throws LocalizedException
     */
    public function getProductCategoriesWithParents(ProductModel $product, ?bool $fromNavigation = false): array
    {
        $productCategoryIds = $product->getCategoryIds();
        $categories = $this->getCategories($productCategoryIds);

        return $this->getCategoryTree($categories, $productCategoryIds, $fromNavigation);
    }

    /**
     * Get product image url
     * TODO: return same image as endpoint
     *
     * @param ProductModel $product
     * @param string|null $size
     * @param string|null $field
     *
     * @return string|null
     */
    public function getProductImageUrl(ProductModel $product, ?string $size = null, ?string $field = 'image'): ?string
    {
        if ($product->hasData($field)) {
            return $this->imageHelper
                ->init($product, 'doofinder_' . $field)
                ->resize($size)
                ->getUrl();
        }

        return null;
    }

    /**
     * Get product price
     *
     * @param ProductModel $product
     * @param string|null $attribute
     * @param boolean|null $tax
     *
     * @return float
     */
    public function getProductPrice(ProductModel $product, ?string $attribute = 'final_price', ?bool $tax = null): float
    {
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
            $tax = $this->taxConfig->getPriceDisplayType() != TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
        }
        if (!$tax) {
            // No tax needed, use base amount
            $value = $amount->getBaseAmount();
        } elseif ($this->taxConfig->priceIncludesTax()) {
            // Tax already included, use value
            $value = $amount->getValue();
        } else {
            // Tax needed but not included in base price
            // Apply tax to base amount to make sure tax is not added twice
            $adjustment = $product->getPriceInfo()->getAdjustment('tax');
            $value = $adjustment->applyAdjustment($amount->getBaseAmount(), $product);
        }

        return $value;
    }

    /**
     * Get product 'out of stock' label
     *
     * @return string
     */
    public function getOutOfStockLabel(): string
    {
        return 'out of stock';
    }

    /**
     * Get product 'in stock' label
     *
     * @return string
     */
    public function getInStockLabel(): string
    {
        return 'in stock';
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
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get attribute type
     *
     * @param ProductModel $product
     * @param string $attributeCode
     * @return string|null
     */
    public function getAttributeType(ProductModel $product, string $attributeCode): ?string
    {
        try {
            $attribute = $this->eavConfig->getAttribute(ProductModel::ENTITY, $attributeCode);
        } catch (LocalizedException $e) {
            return null;
        }
        $optionId = $product->getData($attributeCode);
        if (!$optionId) {
            return null;
        }
        $frontend = $attribute->getFrontend();
        $value = $frontend->getOption($optionId);
        if (!$value) {
            $value = $frontend->getValue($product);
        }

        return gettype($value);
    }

    /**
     * Get attribute
     *
     * @param ProductModel $product
     * @param string $attributeCode
     * @return mixed
     */
    public function getAttribute(ProductModel $product, string $attributeCode)
    {
        try {
            $attribute = $this->eavConfig->getAttribute(ProductModel::ENTITY, $attributeCode);
        } catch (LocalizedException $e) {
            return null;
        }
        $optionId = $product->getData($attributeCode);
        if (!$optionId) {
            return null;
        }
        $frontend = $attribute->getFrontend();
        $value = $frontend->getOption($optionId);
        if (!$value) {
            $value = $frontend->getValue($product);
        }
        if (is_a($value, Phrase::class)) {
            $value = $value->render();
        }

        return $value;
    }

    /**
     * Get attribute text
     *
     * @param ProductModel $product
     * @param string $attributeCode
     * @return string|null
     */
    public function getAttributeText(ProductModel $product, string $attributeCode): ?string
    {
        return $this->getAttribute($product, $attributeCode);
    }

    /**
     * Get attribute as array
     *
     * @param ProductModel $product
     * @param string $attributeCode
     * @return array|null
     */
    public function getAttributeArray(ProductModel $product, string $attributeCode): ?array
    {
        return $this->getAttribute($product, $attributeCode);
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
        $stockItemData = $this->getStockItemData($product->getSku(), $stockId);
        $qty = $stockItemData[GetStockItemDataInterface::QUANTITY];
        $availability = $stockItemData[GetStockItemDataInterface::IS_SALABLE]
            ? $this->getInStockLabel()
            : $this->getOutOfStockLabel();

        return implode(' - ', array_filter([$qty, $availability], function ($item) {
            return $item !== null;
        }));
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
        $stockItemData = $this->getStockItemData($product->getSku(), $stockId);

        return $stockItemData[GetStockItemDataInterface::IS_SALABLE]
            ? $this->getInStockLabel()
            : $this->getOutOfStockLabel();
    }

    /**
     * @param string $sku
     * @param int|null $stockId
     *
     * @return array
     * @throws LocalizedException
     */
    private function getStockItemData(string $sku, ?int $stockId = null): array
    {
        $stockId = $stockId ?? $this->defaultStockProvider->getId();
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        return [
            GetStockItemDataInterface::QUANTITY => $stockItemData[GetStockItemDataInterface::QUANTITY],
            GetStockItemDataInterface::IS_SALABLE => (bool)($stockItemData[GetStockItemDataInterface::IS_SALABLE] ?? false)
        ];
    }
}
