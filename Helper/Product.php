<?php

namespace Doofinder\Feed\Helper;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory = null;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper = null;
    protected $_stockItemRepository;

    /**
     * \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_imageHelper = $imageHelper;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get product id
     *
     * @param \Magento\Catalog\Model\Product
     * @return int
     */
    public function getProductId(\Magento\Catalog\Model\Product $product)
    {
        return $product->getId();
    }

    /**
     * Get product url
     *
     * @todo Use store config
     *
     * @param \Magento\Catalog\Model\Product
     * @return string
     */
    public function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        return $product->getProductUrl(false);
    }

    /**
     * Get product categories
     *
     * @todo This might need some optimalization
     *
     * @param \Magento\Catalog\Model\Product
     * @return \Magento\Catalog\Model\Category[][]
     */
    public function getProductCategoriesWithParents(\Magento\Catalog\Model\Product $product)
    {
        $categoryIds = $product->getResource()->getCategoryIds($product);

        $categoryCollection = $this->_categoryCollectionFactory->create();
        $categoryCollection
            ->addIdFilter($categoryIds)
            ->addAttributeToSelect('name')
            ->load();

        $categories = array();

        foreach ($categoryCollection as $category) {
            $parents = $category->getParentCategories();
            $parents[$category->getId()] = $category;

            $categories[] = $parents;
        }

        return $categories;
    }

    /**
     * Get product image url
     *
     * @todo Use store config
     *
     * @param \Magento\Catalog\Model\Product
     * @return string|null
     */
    public function getProductImageUrl(\Magento\Catalog\Model\Product $product)
    {
        if ($product->hasImage()) {
            return $this->_imageHelper
                ->init($product, 'doofinder_image')
                //->resize()
                ->getUrl();
        }
    }

    /**
     * Get product price
     *
     * @todo This might not work properly with taxes
     * @todo Add proper price rounding
     *
     * @param \Magento\Catalog\Model\Product
     * @return float
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return round($product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(), 2);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductAvailability(\Magento\Catalog\Model\Product $product)
    {
        if ($product->isInStock()) {
            return 'IN STOCK';
        }

        return 'OUT OF STOCK';
    }

    /**
     * @return mixed
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $attributeName
     * @return mixed
     */
    public function getAttributeText(\Magento\Catalog\Model\Product $product, $attributeName)
    {
        return $product->getAttributeText($attributeName);
    }

    /**
     * @todo - we need this?
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getQuantityAndStockStatus(\Magento\Catalog\Model\Product $product)
    {
        $productId = $product->getId();

        $qty = $this->_getStockItem($productId)->getQty();
        $inStock = $this->_getStockItem($productId)->getIsInStock() ? 'In Stock' : 'Out of stock';

        return $qty . ' - ' . $inStock;
    }

    /**
     * @param $productId
     * @return mixed
     */
    protected function _getStockItem($productId)
    {
        return $this->_stockItemRepository->get($productId);
    }
}
