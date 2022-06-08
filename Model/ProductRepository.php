<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model;

use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
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
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Doofinder\Feed\Helper\ProductFactory as ProductHelperFactory;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepository extends \Magento\Catalog\Model\ProductRepository
{
    /**
     * @var ImageFactory
     */
    protected $helperFactory;

    /**
     * @var Emulation
     */
    protected $appEmulation;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var ProductHelperFactory
     */
    private $productHelperFactory;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @param ImageFactory $helperFactory
     * @param Emulation $appEmulation
     * @param StockRegistryInterface $stockRegistry
     * @param ProductHelperFactory $productHelperFactory
     * @param StoreConfig $storeConfig
     * @param ProductFactory $productFactory
     * @param Helper $initializationHelper
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param ProductCollectionFactory $collectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param ProductResourceModel $resourceModel
     * @param ProductLinks $linkInitializer
     * @param ProductLinkTypeProvider $linkTypeProvider
     * @param StoreManagerInterface $storeManager
     * @param ApiFilterBuilder $filterBuilder
     * @param ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param ProductOptionConverter $optionConverter
     * @param Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param ImageContentInterfaceFactory $contentFactory
     * @param MimeTypeExtensionMap $mimeTypeExtensionMap
     * @param ImageProcessorInterface $imageProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param JsonSerializer|null $serializer
     * @param int $cacheLimit
     * @param ReadExtensions|null $readExtensions
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ImageFactory $helperFactory,
        Emulation $appEmulation,
        StockRegistryInterface $stockRegistry,
        ProductHelperFactory $productHelperFactory,
        StoreConfig $storeConfig,
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
        ReadExtensions $readExtensions = null
    ) {
        $this->helperFactory = $helperFactory;
        $this->appEmulation = $appEmulation;
        $this->stockRegistry = $stockRegistry;
        $this->productHelperFactory = $productHelperFactory;
        $this->storeConfig = $storeConfig;
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
            $this->setCustomAttributes($product);
            $this->setExtensionAttributes($product);
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
    public function getList(SearchCriteriaInterface $searchCriteria): ProductSearchResultsInterface
    {
        $searchResult = parent::getList($searchCriteria);
        $storeId = null;

        foreach ($searchResult->getItems() as $product) {
            if ($storeId !== $product->getStoreId()) {
                $storeId = $product->getStoreId();
                $this->appEmulation->stopEnvironmentEmulation();
                $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
            }
            
            $this->setCustomAttributes($product);
            $this->setExtensionAttributes($product);
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
        return $this->helperFactory->create()->init($product, $imageId, $attributes);
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
        $productHelperFactory = $this->productHelperFactory->create();
        $customAttributes = $this->storeConfig->getCustomAttributes($product->getStoreId());
        
        foreach ($customAttributes as $customAttribute){
            $code = $customAttribute['code'];
            if($customAttribute['enabled'] && isset($product[$code])){
                if ("array" === $productHelperFactory->getAttributeType($product, $code)) {
                    $value = $productHelperFactory->getAttributeArray($product, $code);
                } else {
                    $value = $productHelperFactory->getAttributeText($product, $code);
                }
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
    }

    /**
     * Function to add the extension attributes to the product
     * 
     * @param ProductInterface $product
     * @return void
     */
    private function setExtensionAttributes($product): void
    {
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setStockItem($this->stockRegistry->getStockItem($product->getId()));
        $extensionAttributes->setUrlFull($this->getProductUrl($product));
        if($product->getTypeId() == Configurable::TYPE_CODE){
            $extensionAttributes->setFinalPrice($product->getFinalPrice());
        }
        $product->setExtensionAttributes($extensionAttributes);
    }
}