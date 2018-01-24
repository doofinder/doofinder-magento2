<?php

namespace Doofinder\Feed\Model\Generator\Map\Product;

use \Doofinder\Feed\Model\Generator\Map\Product;

/**
 * Grouped product map
 */
class Grouped extends Product
{
    /**
     * Get bundle product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @param boolean $minimal
     * @return float
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product, $field, $minimal = false)
    {
        if ($field == 'final_price') {
            $minimal = true;
        }

        return parent::getProductPrice($product, $field, $minimal);
    }
}
