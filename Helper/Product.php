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

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_imageHelper = $imageHelper;
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
        return round($product->getPrice(), 2);
    }
}
