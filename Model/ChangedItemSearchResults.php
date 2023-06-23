<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model;

use Doofinder\Feed\Api\Data\ChangedItemSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class ChangedItemSearchResults extends SearchResults implements ChangedItemSearchResultsInterface
{

}
