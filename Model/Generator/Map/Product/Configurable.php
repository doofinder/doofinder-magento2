<?php

namespace Doofinder\Feed\Model\Generator\Map\Product;

use Doofinder\Feed\Model\Generator\Map\Product as MapProduct;
use Magento\Catalog\Model\Product;

/**
 * Configurable product map
 */
class Configurable extends MapProduct
{
    /**
     * @param Product $product
     * @param string $field
     * @return mixed
     */
    public function getProductPrice(Product $product, $field)
    {
        if ($field == 'final_price') {
            // Magento will return final price properly
            return parent::getProductPrice($product, $field);
        }

        // for other price types, use children's price
        $prices = [];
        $usedProds = $product->getTypeInstance(true)->getUsedProducts($product);
        foreach ($usedProds as $child) {
            if ($child->getId() != $product->getId()) {
                $prices[] = parent::getProductPrice($child, $field);
            }
        }

        $prices = array_filter($prices, function ($price) {
            return is_numeric($price);
        });

        return !empty($prices) ? min($prices) : null;
    }
}
