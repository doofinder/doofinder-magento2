<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ChangedProductSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return ChangedProductInterface[]
     */
    public function getItems();

    /**
     * @param ChangedProductInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
