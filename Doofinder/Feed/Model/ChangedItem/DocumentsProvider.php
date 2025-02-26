<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\ChangedItem;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedItem\Collection as ChangedItemCollection;
use Generator;

class DocumentsProvider
{

    /**
     * Gets the response batched
     *
     * @param ChangedItemCollection $collection
     * @return Generator
     */
    public function getBatched(ChangedItemCollection $collection): Generator
    {
        if ($collection->getSize() === 0) {
            yield [];
        }

        foreach ($collection as $item) {
            yield $item[ChangedItemInterface::ITEM_ID];
        }
    }
}
