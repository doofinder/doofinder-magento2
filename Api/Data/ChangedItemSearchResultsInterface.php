<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ChangedItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return ChangedItemInterface[]
     */
    public function getItems();

    /**
     * @param ChangedItemInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
