<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\ChangedProduct;

use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct\Collection as ChangedProductCollection;
use Generator;

class DocumentsProvider
{

    /**
     * @param ChangedProductCollection $collection
     * @return Generator
     */
    public function getBatched(ChangedProductCollection $collection): Generator
    {
        if ($collection->getSize() === 0) {
            yield [];
        }

        foreach ($collection as $item) {
            yield $item[ChangedProductInterface::PRODUCT_ID];
        }
    }
}
