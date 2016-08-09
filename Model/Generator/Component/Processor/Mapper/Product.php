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
            case 'url_key':
                return $this->getProductUrl($this->_context);

            case 'category_ids':
                return $this->getProductCategories($this->_context);

            case 'image':
                return $this->getProductImage($this->_context);

            case 'price':
                return $this->getProductPrice($this->_context);
        }

        return parent::processDefinition($definition);
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
}
