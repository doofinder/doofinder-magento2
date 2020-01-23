<?php

namespace Doofinder\Feed\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;

/**
 * Provides helper functionality for DelayedUpdates.
 */
class DelayedUpdates
{
    /**
     * @var ProductVisibility $productVisibility
     */
    private $productVisibility;

    /**
     * A constructor.
     *
     * @param ProductVisibility $productVisibility
     */
    public function __construct(
        ProductVisibility $productVisibility
    ) {
        $this->productVisibility = $productVisibility;
    }

    /**
     * Determines whether the product is visible in search results.
     *
     * @param Product $product
     *
     * @return boolean
     */
    public function isProductVisible(Product $product)
    {
        return in_array(
            $product->getVisibility(),
            $this->productVisibility->getVisibleInSiteIds()
        );
    }
}
