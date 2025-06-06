<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model;

use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository as ProductRepositoryBase;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\StoreConfigManagerInterface as MagentoStoreConfig;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Doofinder\Feed\Helper\ProductFactory as ProductHelperFactory;
use Doofinder\Feed\Helper\PriceFactory as PriceHelperFactory;
use Doofinder\Feed\Helper\InventoryFactory as InventoryHelperFactory;
use Doofinder\Feed\Helper\StoreConfig;

class ProductRepository implements \Magento\Catalog\Api\ProductRepositoryInterface
{
    /** @var \Doofinder\Feed\Helper\ImageFactory */
    protected $imageHelperFactory;

    /** @var \Magento\Store\Model\App\Emulation */
    protected $appEmulation;

    /** @var \Magento\Catalog\Api\CategoryListInterface */
    protected $categoryListInterface;

    /** @var \Doofinder\Feed\Helper\ProductFactory */
    protected $productHelperFactory;

    /** @var \Doofinder\Feed\Helper\PriceFactory */
    protected $priceHelperFactory;

    /** @var \Doofinder\Feed\Helper\InventoryFactory */
    protected $inventoryHelperFactory;

    /** @var \Doofinder\Feed\Model\Store\Config */
    protected $storeConfig;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $magentoStoreConfig;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var \Magento\Catalog\Model\ProductFactory */
    protected $productFactory;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var \Magento\Catalog\Model\ResourceModel\Product */
    protected $resourceModel;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Catalog\Model\ProductRepository */
    protected $productRepositoryBase;

    /** @var int */
    protected $cacheLimit;

    /** @var array */
    protected $instances = [];

    /** @var array */
    protected $instancesById = [];

    /** @var string[] */
    protected $excludedCustomAttributes = [];

    /** @var \Magento\Framework\Serialize\Serializer\Json|null */
    private $serializer;

    /**
     * ProductRepository constructor.
     *
     * @param ImageFactory $imageHelperFactory Factory for creating image helper instances.
     * @param Emulation $appEmulation Application emulation for store context.
     * @param CategoryListInterface $categoryListInterface Category list interface.
     * @param ProductHelperFactory $productHelperFactory Factory for product helper instances.
     * @param PriceHelperFactory $priceHelperFactory Factory for price helper instances.
     * @param InventoryHelperFactory $inventoryHelperFactory Factory for inventory helper instances.
     * @param StoreConfig $storeConfig Custom module store config.
     * @param MagentoStoreConfig $magentoStoreConfig Magento core store config.
     * @param ScopeConfigInterface $scopeConfig Magento core scope config.
     * @param ProductFactory $productFactory Product factory instance.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder Search criteria builder.
     * @param ProductResourceModel $resourceModel Product resource model.
     * @param StoreManagerInterface $storeManager Store manager interface.
     * @param ProductRepositoryBase $productRepositoryBase Base product repository.
     * @param int $cacheLimit Product cache size limit (default: 1000).
     * @param Json|null $serializer JSON serializer (optional).
     */
    public function __construct(
        ImageFactory $imageHelperFactory,
        Emulation $appEmulation,
        CategoryListInterface $categoryListInterface,
        ProductHelperFactory $productHelperFactory,
        PriceHelperFactory $priceHelperFactory,
        InventoryHelperFactory $inventoryHelperFactory,
        StoreConfig $storeConfig,
        MagentoStoreConfig $magentoStoreConfig,
        ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductResourceModel $resourceModel,
        StoreManagerInterface $storeManager,
        ProductRepositoryBase $productRepositoryBase,
        $cacheLimit = 1000,
        ?Json $serializer = null
    ) {
        $this->imageHelperFactory = $imageHelperFactory;
        $this->appEmulation = $appEmulation;
        $this->categoryListInterface = $categoryListInterface;
        $this->productHelperFactory = $productHelperFactory;
        $this->priceHelperFactory = $priceHelperFactory;
        $this->inventoryHelperFactory = $inventoryHelperFactory;
        $this->storeConfig = $storeConfig;
        $this->magentoStoreConfig = $magentoStoreConfig;
        $this->scopeConfig = $scopeConfig;
        $this->productFactory = $productFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->storeManager = $storeManager;
        $this->productRepositoryBase = $productRepositoryBase;
        $this->cacheLimit = $cacheLimit;
        $this->instances = [];
        $this->instancesById = [];
        //Add here any custom attributes we want to exclude from indexation
        $this->excludedCustomAttributes = ['special_price', 'special_from_date', 'special_to_date'];
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @inheritDoc
     */
    public function get($sku, $editMode = false, $storeId = null, $forceReload = false): ProductInterface
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        $cachedProduct = $this->getProductFromLocalCache($sku, $cacheKey);
        if ($cachedProduct === null || $forceReload) {
            $product = $this->productFactory->create();
            $productId = $this->resourceModel->getIdBySku($sku);
            if (!$productId) {
                throw new NoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                );
            }
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            } else {
                // Start Custom code here
                $storeId = $this->storeManager->getStore()->getId();
            }
            $product->load($productId);
            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
            $this->setExtensionAttributes($product, $storeId);
            $this->setCustomAttributes($product);
            $this->appEmulation->stopEnvironmentEmulation();
            // End Custom code here
            $this->cacheProduct($cacheKey, $product);
            $cachedProduct = $product;
        }

        return $cachedProduct;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->productRepositoryBase->getList($searchCriteria);
        $storeId = null;

        foreach ($searchResult->getItems() as $product) {
            if ($storeId !== $product->getStoreId()) {
                $storeId = $product->getStoreId();
                $this->appEmulation->stopEnvironmentEmulation();
                $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
            }

            $this->setExtensionAttributes($product, $storeId);
            $this->setCustomAttributes($product);
        }
        $this->appEmulation->stopEnvironmentEmulation();

        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function save(ProductInterface $product, $saveOptions = false)
    {
        return $this->productRepositoryBase->save($product, $saveOptions);
    }

    /**
     * @inheritdoc
     */
    public function delete(ProductInterface $product)
    {
        return $this->productRepositoryBase->delete($product);
    }

    /**
     * @inheritdoc
     */
    public function getById($productId, $editMode = false, $storeId = null, $forceReload = false)
    {
        return $this->productRepositoryBase->getById($productId, $editMode, $storeId, $forceReload);
    }

    /**
     * @inheritdoc
     */
    public function deleteById($sku)
    {
        return $this->productRepositoryBase->deleteById($sku);
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    public function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * Resets the cached product instances and their indexed IDs.
     *
     * This method clears both $instances and $instancesById arrays,
     * ensuring a fresh state for product processing.
     *
     * @return void
     */
    public function _resetState()
    {
        $this->instances = [];
        $this->instancesById = [];
    }

    /**
     * Retrieve product image
     *
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     * @return ImageHelper
     */
    private function getImage(Product $product, string $imageId, ?array $attributes = []): ImageHelper
    {
        return $this->imageHelperFactory->create()->init($product, $imageId, $attributes);
    }

    /**
     * Retrieve the product URL
     *
     * @param Product $product
     * @return String
     */
    private function getProductUrl(Product $product): string
    {
        return $this->productHelperFactory->create()->getProductUrl($product);
    }

    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param ProductInterface $product
     * @return void
     */
    private function cacheProduct(string $cacheKey, ProductInterface $product): void
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->saveProductInLocalCache($product, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, (int) $offset, null, true);
            $this->instances = array_slice($this->instances, (int) $offset, null, true);
        }
    }

    /**
     * Gets product from the local cache by SKU.
     *
     * @param string $sku
     * @param string $cacheKey
     * @return Product|null
     */
    private function getProductFromLocalCache(string $sku, string $cacheKey): ?Product
    {
        $preparedSku = $this->prepareSku($sku);

        return $this->instances[$preparedSku][$cacheKey] ?? null;
    }

    /**
     * Saves product in the local cache by sku.
     *
     * @param ProductInterface $product
     * @param string $cacheKey
     * @return void
     */
    private function saveProductInLocalCache(ProductInterface $product, string $cacheKey): void
    {
        $preparedSku = $this->prepareSku($product->getSku());
        $this->instances[$preparedSku][$cacheKey] = $product;
    }

    /**
     * Converts SKU to lower case and trims.
     *
     * @param string $sku
     * @return string
     */
    private function prepareSku(string $sku): string
    {
        return mb_strtolower(trim($sku));
    }

    /**
     * Function to update the custom attributes of a product depending on the custom attributes selection stored
     * in the config table.
     * Here we will update also the value of the custom attribute (id of the option selected) by the option text.
     *
     * @param ProductInterface $product
     * @return void
     */
    private function setCustomAttributes($product): void
    {
        $productHelper = $this->productHelperFactory->create();
        $customAttributes = $this->storeConfig->getCustomAttributes($product->getStoreId());

        foreach ($customAttributes as $customAttribute) {
            $code = $customAttribute['code'];
            if ($customAttribute['enabled'] && isset($product[$code])) {
                ("array" === $productHelper->getAttributeType($product, $code)) ?
                    $value = $productHelper->getAttributeArray($product, $code) :
                    $value = $productHelper->getAttributeText($product, $code);

                $product->setCustomAttribute($code, $value);
            } else {
                unset($product[$code]);
            }
        }

        // Fields that we want to send always as custom attributes
        $thumbnailImageUrl = $this->getImage($product, 'product_thumbnail_image')->getUrl();
        $product->setCustomAttribute('thumbnail', $thumbnailImageUrl);
        $smallImageUrl = $this->getImage($product, 'product_small_image')->getUrl();
        $product->setCustomAttribute('small_image', $smallImageUrl);
        $this->removeExcludedCustomAttributes($product);
    }

    /**
     * Function to add the extension attributes to the product
     *
     * @param ProductInterface $product
     * @param int $storeId
     * @return void
     */
    private function setExtensionAttributes($product, $storeId): void
    {
        $priceHelper = $this->priceHelperFactory->create();
        $productHelper = $this->productHelperFactory->create();
        $inventoryHelper = $this->inventoryHelperFactory->create();
        $storeCode = $this->storeManager->getStore($storeId)->getCode();

        $extensionAttributes = $product->getExtensionAttributes();

        $stockId = (int) $inventoryHelper->getStockIdByStore((int) $storeId);
        $stockAndStatus = $inventoryHelper->getQuantityAndAvailability($product, $stockId);

        $extensionAttributes->setUrlFull($this->getProductUrl($product));
        $extensionAttributes->setIsInStock($stockAndStatus[1]);
        $extensionAttributes->setBaseUrl($this->magentoStoreConfig->getStoreConfigs([$storeCode])[0]->getBaseUrl());
        $enabledCfgLinks = $this->getEnabledConfigurableLinks($extensionAttributes->getConfigurableProductLinks());
        $extensionAttributes->setConfigurableProductLinks($enabledCfgLinks);
        $extensionAttributes->setBaseMediaUrl(
            $this->magentoStoreConfig->getStoreConfigs([$storeCode])[0]->getBaseMediaUrl()
        );

        $categories = $extensionAttributes->getCategoryLinks();
        if (is_array($categories)) {
            $extensionAttributes->setCategoryLinks($this->getCategoriesInformation($categories));
        }

        $price = round($priceHelper->getProductPrice($product, 'regular_price'), 2);
        $specialPrice = round($priceHelper->getProductPrice($product, 'final_price'), 2);
        $extensionAttributes->setPrice($price);
        ($price == $specialPrice || $specialPrice == 0) ?: $extensionAttributes->setSpecialPrice($specialPrice, 2);

        $extensionAttributes->setImage($productHelper
            ->getProductImageUrl(
                $product,
                $this
                    ->storeConfig
                    ->getValueFromConfig("doofinder_config_config/doofinder_image/doofinder_image_size")
            ));

        $configurableProductsOptions = $extensionAttributes->getConfigurableProductOptions();
        $extensionAttributes->setConfigurableProductOptions(
            $this->updateConfigurableProductOptions($configurableProductsOptions)
        );

        $product->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Filters a list of configurable product link IDs to return only those that are enabled.
     *
     * Loads each product by ID and checks its status. Only products with status `STATUS_ENABLED`
     * are included in the returned array.
     *
     * @param int[]|null $configurableLinksIds Array of product IDs or null.
     * @return int[] Array of enabled product IDs.
     */
    private function getEnabledConfigurableLinks($configurableLinksIds)
    {
        $enabledProductIds = [];

        if (null === $configurableLinksIds) {
            return $enabledProductIds;
        }

        foreach ($configurableLinksIds as $productId) {
            $product = $this->productFactory->create()->load($productId);
            if (Status::STATUS_ENABLED !== (int) $product->getStatus()) {
                continue;
            }

            $enabledProductIds[] = $productId;
        }

        return $enabledProductIds;
    }

    /**
     * Function to remove the excluded custom_attributes.
     *
     * @param ProductInterface $product
     * @return void
     */
    private function removeExcludedCustomAttributes($product)
    {
        foreach ($this->excludedCustomAttributes as $attribute) {
            if (isset($product[$attribute])) {
                unset($product[$attribute]);
            }
        }
    }

    /**
     * Retrieves relevant category information for a set of categories, including their parent categories.
     *
     * This method processes a list of categories (either arrays or objects), extracts their IDs,
     * finds their full paths to include parent categories, and then retrieves a filtered and
     * reduced set of category data (only id, name, and parent id) to minimize payload size.
     *
     * @param array $categories List of categories as arrays or objects implementing getCategoryId().
     * @return mixed[] Array of simplified category data with keys: category_id, entity_id, name, parent_id.
     */
    private function getCategoriesInformation($categories)
    {
        $categoryIds = [];
        foreach ($categories as $category) {
            if (is_array($category)) {
                $categoryIds[$category['category_id']] = true;
            } else {
                $categoryIds[$category->getCategoryId()] = true;
            }
        }

        // Get table name with prefix if it exists
        $catalogCategoryEntityTable = $this->resourceModel->getTable('catalog_category_entity');

        // Load paths of product categories to load their parents
        $connection = $this->resourceModel->getConnection();
        $categoryPaths = $connection->fetchCol(
            $connection->select()
                ->from($catalogCategoryEntityTable, ['path'])
                ->where('entity_id IN (?)', array_keys($categoryIds))
        );

        // Scope category collection with current store Root category
        $storeRootPath = '1/' . $this->storeManager->getStore()->getRootCategoryId() . '/';

        // Get all category Ids (with parents Ids from paths)
        $categoryIdsWithParents = array_reduce($categoryPaths, function ($acc, $path) use ($storeRootPath) {
            $path = str_replace($storeRootPath, '', $path);
            $ids = explode('/', $path);
            return array_unique(array_merge($acc, $ids));
        }, []);

        // Obtain the results using only the ids from categories and their parents
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $categoryIdsWithParents, 'in')
            ->addFilter('is_active', '1')
            ->create();

        $categories = $this->categoryListInterface->getList($searchCriteria)->__toArray();
        $categoryUrlSuffix = $this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        // Get just the information needed in order to make the response lighter
        $categoryResults = [];
        foreach ($categories["items"] as $category) {
            $urlPath = $category['url_path'] ?? '';
            if ($categoryUrlSuffix && substr($urlPath, -strlen($categoryUrlSuffix)) != $categoryUrlSuffix) {
                $urlPath .= $categoryUrlSuffix;
            }

            $categoryResults[] = [
                'category_id' => $category['entity_id'],
                'entity_id' => $category['entity_id'],
                'name' => $category['name'],
                'parent_id' => $category['parent_id'],
                'url_path' => $urlPath
            ];
        }

        return $categoryResults;
    }

    /**
     * Formats and enriches configurable product options with attribute codes.
     *
     * This method uses the EAV config to retrieve attribute codes based on attribute IDs
     * and assembles a simplified array of options containing only relevant information.
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute[]|null $configurableProductsOptions
     *        Array of configurable product option objects or null.
     *
     * @return mixed[] Each array contains:
     *                 - attribute_id: int
     *                 - label: string
     *                 - code: string (attribute code)
     *                 - product_id: int
     */
    private function updateConfigurableProductOptions($configurableProductsOptions)
    {
        $eavConfig = ObjectManager::getInstance()->get(\Magento\Eav\Model\Config::class);
        $configurableProductsOptionsResult = [];

        if ($configurableProductsOptions != null) {
            foreach ($configurableProductsOptions as $configurableProductOption) {
                $attribute = $eavConfig->getAttribute('catalog_product', $configurableProductOption->getAttributeId());
                $configurableProductsOptionsResult[] = [
                    'attribute_id' => $configurableProductOption->getAttributeId(),
                    'label' => $configurableProductOption->getLabel(),
                    'code' => $attribute->getAttributeCode(),
                    'product_id' => $configurableProductOption->getProductId()
                ];
            }
        }
        return $configurableProductsOptionsResult;
    }
}
