<?php

namespace Doofinder\Feed\Test\Helper;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Helper class
 */
class Product
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductCollectionFactory */
    private $productCollectionFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    public function __construct() {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productCollectionFactory = $this->objectManager->create(ProductCollectionFactory::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true, true);
    }

    public function deleteAllProducts() {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        foreach($collection->getItems() as $item) {
            $product = $this->productRepository->get($item->getSku());
            $product->delete();
        }
    }
}