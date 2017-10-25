<?php

namespace Doofinder\Feed\Model\Generator\Component\Fetcher\Product;

use \Doofinder\Feed\Model\Generator\Component\Fetcher\Product;

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

        $this->_isStarted = true;
        $this->_isDone = true;
        $this->_lastEntityId = end($products)->getEntityId();
        $this->_itemsLeftCount = 0;

        return $products;
    }
}
