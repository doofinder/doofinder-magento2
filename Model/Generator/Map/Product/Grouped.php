<?php

namespace Doofinder\Feed\Model\Generator\Map\Product;

use \Doofinder\Feed\Model\Generator\Map\Product;

/**
 * Class Grouped
 *
 * @package Doofinder\Feed\Model\Generator\Map\Product
 */
class Grouped extends Product
{
    /**
     * Get bundle product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    protected function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
    }
}
