<?php

declare(strict_types=1);

namespace Doofinder\Feed\Api\Data\Generator;

use Magento\Catalog\Model\Product;

/**
 * Interface MapInterface
 * The interface for getting product data in Doofinder Indexer Fetcher
 */
interface MapInterface
{
    /**
     * Get product field data
     * @param Product $product
     * @param string $field
     * @return mixed
     */
    public function get(Product $product, string $field);
}
