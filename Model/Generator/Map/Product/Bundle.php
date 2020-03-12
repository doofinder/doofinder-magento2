<?php

namespace Doofinder\Feed\Model\Generator\Map\Product;

use Doofinder\Feed\Model\Generator\Map\Product as MapProduct;
use Magento\Catalog\Model\Product;

/**
 * Bundle product map
 */
class Bundle extends MapProduct
{
    /**
     * @param Product $product
     * @param string $field
     * @return mixed
     */
    public function getProductPrice(Product $product, $field)
    {
        if ($field == 'special_price') {
            $field = 'final_price';
        }
        return parent::getProductPrice($product, $field);
    }
}
