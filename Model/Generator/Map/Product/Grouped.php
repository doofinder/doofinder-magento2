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
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @param boolean $minimal = false
     * @return float
     */
    protected function getProductPrice(\Magento\Catalog\Model\Product $product, $field, $minimal = false)
    {
        /** @fixme We should not do this like here but there is a bug in core */
        if (in_array($field, ['price', 'final_price'])) {
            return $product->getProductPrice();
        }

        return parent::getProductPrice($product, $field, $minimal);
    }
}
