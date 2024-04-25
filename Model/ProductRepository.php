<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model;

use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\LinkTypeProvider as ProductLinkTypeProvider;
use Magento\Catalog\Model\Product\Option\Converter as ProductOptionConverter;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\FilterBuilder as ApiFilterBuilder;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Api\StoreConfigManagerInterface as MagentoStoreConfig;
use Magento\Store\Model\StoreManagerInterface;
use Doofinder\Feed\Helper\ProductFactory as ProductHelperFactory;
use Doofinder\Feed\Helper\PriceFactory as PriceHelperFactory;
use Doofinder\Feed\Helper\InventoryFactory as InventoryHelperFactory;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepository extends \Magento\Catalog\Model\ProductRepository
{
    protected $imageHelperFactory;
    protected $appEmulation;
    private $stockRegistry;
    private $cacheLimit = 0;
    private $productHelperFactory;
    private $priceHelperFactory;
    private $inventoryHelperFactory;
    private $storeConfig;
    private $magentoStoreConfig;
    private $excludedCustomAttributes;
    private $categoryListInterface;
    private $productMetadataInterface;

    public function __construct(
        ImageFactory $imageHelperFactory,
        Emulation $appEmulation,
        StockRegistryInterface $stockRegistry,
        CategoryListInterface $categoryListInterface,
        ProductHelperFactory $productHelperFactory,
        PriceHelperFactory $priceHelperFactory,
        InventoryHelperFactory $inventoryHelperFactory,
        StoreConfig $storeConfig,
        MagentoStoreConfig $magentoStoreConfig,
        ProductFactory $productFactory,
        Helper $initializationHelper,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        ProductCollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductAttributeRepositoryInterface $attributeRepository,
        ProductResourceModel $resourceModel,
        ProductLinks $linkInitializer,
        ProductLinkTypeProvider $linkTypeProvider,
        StoreManagerInterface $storeManager,
        ApiFilterBuilder $filterBuilder,
        ProductAttributeRepositoryInterface $metadataServiceInterface,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        ProductOptionConverter $optionConverter,
        Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        ImageContentInterfaceFactory $contentFactory,
        MimeTypeExtensionMap $mimeTypeExtensionMap,
        ImageProcessorInterface $imageProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null,
        JsonSerializer $serializer = null,
        $cacheLimit = 1000,
        ReadExtensions $readExtensions = null,
        ProductMetadataInterface $productMetadataInterface
    ) {
        $this->imageHelperFactory = $imageHelperFactory;
        $this->appEmulation = $appEmulation;
        $this->stockRegistry = $stockRegistry;
        $this->categoryListInterface = $categoryListInterface;
        $this->productHelperFactory = $productHelperFactory;
        $this->priceHelperFactory = $priceHelperFactory;
        $this->inventoryHelperFactory = $inventoryHelperFactory;
        $this->storeConfig = $storeConfig;
        $this->magentoStoreConfig = $magentoStoreConfig;
        $this->productMetadataInterface = $productMetadataInterface;
        //Add here any custom attributes we want to exclude from indexation
        $this->excludedCustomAttributes = ['special_price', 'special_from_date', 'special_to_date'];
        if (method_exists($this->productMetadataInterface, 'getVersion') && 
            version_compare($this->productMetadataInterface->getVersion(), '2.4.7', '>=')) {
            parent::__construct(
                $productFactory,
                $searchResultsFactory,
                $collectionFactory,
                $searchCriteriaBuilder,
                $attributeRepository,
                $resourceModel,
                $linkInitializer,
                $linkTypeProvider,
                $storeManager,
                $filterBuilder,
                $metadataServiceInterface,
                $extensibleDataObjectConverter,
                $optionConverter,
                $fileSystem,
                $contentValidator,
                $contentFactory,
                $mimeTypeExtensionMap,
                $imageProcessor,
                $extensionAttributesJoinProcessor,
                $collectionProcessor,
                $serializer,
                $cacheLimit,
                $readExtensions
            );
        } else {
            parent::__construct(
                $productFactory,
                $initializationHelper,
                $searchResultsFactory,
                $collectionFactory,
                $searchCriteriaBuilder,
                $attributeRepository,
                $resourceModel,
                $linkInitializer,
                $linkTypeProvider,
                $storeManager,
                $filterBuilder,
                $metadataServiceInterface,
                $extensibleDataObjectConverter,
                $optionConverter,
                $fileSystem,
                $contentValidator,
                $contentFactory,
                $mimeTypeExtensionMap,
                $imageProcessor,
                $extensionAttributesJoinProcessor,
                $collectionProcessor,
                $serializer,
                $cacheLimit,
                $readExtensions
            );
        }
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
        $searchResult = parent::getList($searchCriteria);
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
    private function getProductUrl(Product $product): String
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
            $this->instancesById = array_slice($this->instancesById, (int)$offset, null, true);
            $this->instances = array_slice($this->instances, (int)$offset, null, true);
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
                    $value = $productHelper->getAttributeText($product, $code)  ;

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

        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $product->getExtensionAttributes();

        $stockId = (int)$inventoryHelper->getStockIdByStore((int)$storeId);
        $stockAndStatus = $inventoryHelper->getQuantityAndAvailability($product, $stockId);

        $extensionAttributes->setUrlFull($this->getProductUrl($product));
        $extensionAttributes->setIsInStock($stockAndStatus[1]);
        $extensionAttributes->setBaseUrl($this->magentoStoreConfig->getStoreConfigs([$storeCode])[0]->getBaseUrl());
        $extensionAttributes->setBaseMediaUrl(
            $this->magentoStoreConfig->getStoreConfigs([$storeCode])[0]->getBaseMediaUrl()
        );

        $categories =  $extensionAttributes->getCategoryLinks();
        if (is_array($categories)) {
            $extensionAttributes->setCategoryLinks($this->getCategoriesInformation($categories));
        }

        $price = round($priceHelper->getProductPrice($product, 'regular_price'), 2);
        $specialPrice = round($priceHelper->getProductPrice($product, 'final_price'), 2);
        $extensionAttributes->setPrice($price);
        ($price == $specialPrice || $specialPrice == 0) ?: $extensionAttributes->setSpecialPrice($specialPrice, 2);

        $extensionAttributes->setImage($productHelper->getProductImageUrl($product));

        $product->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Function to remove the excluded custom_attributes.
     *
     * @param ProductInterface $product
     * @return void
     */
    public function removeExcludedCustomAttributes($product)
    {
        foreach ($this->excludedCustomAttributes as $attribute) {
            if (isset($product[$attribute])) {
                unset($product[$attribute]);
            }
        }
    }

    private function getCategoriesInformation($categories)
    {
        $categoryIds = [];
        foreach ($categories as $category) {
            if(is_array($category)){
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

        // Get just the information needed in order to make the response lighter
        $categoryResults = [];
        foreach ($categories["items"] as $category) {
            $categoryResults[] = [
                'category_id' => $category['entity_id'],
                'entity_id' => $category['entity_id'],
                'name' => $category['name'],
                'parent_id' => $category['parent_id']
            ];
        }

        return $categoryResults;
    }
}
