<?php

namespace Doofinder\Feed\Model\Generator\Map\Product;

use \Doofinder\Feed\Model\Generator\Map\Product;

/**
 * Class Bundle
 *
 * @package Doofinder\Feed\Model\Generator\Map\Product
 */
class Bundle extends Product
{
    /**
     * Get bundle product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @param boolean $minimal = true
     * @return float
     */
    protected function getProductPrice(\Magento\Catalog\Model\Product $product, $field, $minimal = true)
    {
        return parent::getProductPrice($product, $field, $minimal);
    }
}
