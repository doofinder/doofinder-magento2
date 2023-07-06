<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ChangedItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Obtains the resulting items from a search
     * 
     * @return ChangedItemInterface[]
     */
    public function getItems();

    /**
     * Sets the items inside the attribute
     * 
     * @param ChangedItemInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
