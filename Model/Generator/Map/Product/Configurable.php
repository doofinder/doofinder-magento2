<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Generator\Map\Product;

use Doofinder\Feed\Model\Generator\Map\Product as ProductMap;
use Magento\Catalog\Model\Product;

class Configurable extends ProductMap
{
    /**
     * @param Product $product
     * @param string $field
     * @return float|null
     */
    public function getProductPrice(Product $product, string $field): ?float
    {
        if ($field == 'final_price') {
            // Magento will return final price properly
            return parent::getProductPrice($product, $field);
        }
        // for other price types, use children's price
        $prices = [];
        $usedProds = $product->getTypeInstance()->getUsedProducts($product);
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

    /**
     * @inheritDoc
     */
    public function getGroupingId(Product $product): ?string
    {
        return (string)$product->getId();
    }
}
