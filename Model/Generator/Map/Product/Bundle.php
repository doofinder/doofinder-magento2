<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Generator\Map\Product;

use Doofinder\Feed\Model\Generator\Map\Product as ProductMap;
use Magento\Catalog\Model\Product as ProductModel;

class Bundle extends ProductMap
{
    /**
     * @param ProductModel $product
     * @param string $field
     *
     * @return float|null
     */
    public function getProductPrice(ProductModel $product, string $field): ?float
    {
        if ($field == 'special_price') {
            $field = 'final_price';
        }

        return parent::getProductPrice($product, $field);
    }
}
