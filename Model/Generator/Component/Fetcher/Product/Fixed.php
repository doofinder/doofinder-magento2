<?php

namespace Doofinder\Feed\Model\Generator\Component\Fetcher\Product;

use \Doofinder\Feed\Model\Generator\Component\Fetcher\Product;

/**
 * Fixed product fetcher
 */
class Fixed extends Product
{
    /**
     * Returns fixed products
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function fetchProducts()
    {
        $products = $this->getData('products');

        $this->isStarted = true;
        $this->isDone = true;
        $this->lastEntityId = end($products)->getEntityId();
        $this->itemsLeftCount = 0;

        return $products;
    }
}
