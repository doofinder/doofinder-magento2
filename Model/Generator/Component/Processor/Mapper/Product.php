<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor\Mapper;

use \Doofinder\Feed\Model\Generator\Component\Processor\Mapper;

class Product extends Mapper
{
    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    protected $_helper = null;

    public function __construct(
        \Doofinder\Feed\Helper\Product $helper,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($logger, $data);
    }

    /**
     * Process item
     *
     * @param \Doofinder\Feed\Model\Generator\Item
     */
    protected function processItem(\Doofinder\Feed\Model\Generator\Item $item)
    {
        if (!is_a($item->getContext(), '\Magento\Catalog\Model\Product')) {
            $this->_logger->warning('Item context is not a product');
            return;
        }

        parent::processItem($item);
    }

    /**
     * Get mapped field value
     *
     * @param array
     * @return mixed
     */
    protected function processDefinition(array $definition)
    {
        switch ($definition['field']) {
            case 'df_id':
                return $this->getProductId($this->_context);

            case 'url_key':
                return $this->getProductUrl($this->_context);

            case 'category_ids':
                return $this->getProductCategories($this->_context);

            case 'image':
                return $this->getProductImage($this->_context);

            case 'price':
                return $this->getProductPrice($this->_context);
            
            case 'availability':
                return $this->getProductAvailability($this->_context);

            case 'currency':
                return $this->getCurrencyCode();

            case 'color':
                return $this->getProductColor($this->_context);

            case 'tax_class_id':
                return $this->getTaxName($this->_context);

            case 'manufacturer':
                return $this->getManufacturer($this->_context);

            case 'weight_type':
                return $this->getWeightType($this->_context);

            case 'quantity_and_stock_status':
                return $this->getQuantityAndStockStatus($this->_context);
        }

        return parent::processDefinition($definition);
    }

    /**
     * Get product id
     *
     * @param \Magento\Catalog\Model\Product
     * @return int
     */
    protected function getProductId(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductId($product);
    }

    /**
     * Get product url
     *
     * @param \Magento\Catalog\Model\Product
     * @return string
     */
    protected function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductUrl($product);
    }

    /**
     * Get product categories
     *
     * @todo This might need some optimalization
     *
     * @param \Magento\Catalog\Model\Product
     * @return string
     */
    protected function getProductCategories(\Magento\Catalog\Model\Product $product)
    {
        $categories = $this->_helper->getProductCategoriesWithParents($product);

        $entries = [];
        foreach ($categories as $entry) {
            $names = [];

            foreach ($entry as $category) {
                $names[] = $category->getName();
            }

            $entries[] = implode(' > ', $names);
        }

        return implode(' %% ', $entries);
    }

    /**
     * Get product image
     *
     * @todo Use store config
     *
     * @param \Magento\Catalog\Model\Product
     * @return string|null
     */
    protected function getProductImage(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductImageUrl($product);
    }

    /**
     * Get product price
     *
     * @param \Magento\Catalog\Model\Product
     * @return string|null
     */
    protected function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return number_format($this->_helper->getProductPrice($product), 2, '.', '');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    protected function getProductAvailability(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getProductAvailability($product);
    }

    /**
     * @return mixed
     */
    protected function getCurrencyCode()
    {
        return $this->_helper->getCurrencyCode();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    protected function getProductColor(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getAttributeText($product, 'color');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    protected function getTaxName(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getAttributeText($product, 'tax_class_id');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    protected function getManufacturer(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getAttributeText($product, 'manufacturer');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    protected function getWeightType(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getAttributeText($product, 'weight_type');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getQuantityAndStockStatus(\Magento\Catalog\Model\Product $product)
    {
        return $this->_helper->getQuantityAndStockStatus($product);
    }
}
