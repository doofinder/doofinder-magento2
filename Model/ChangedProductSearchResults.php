<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model;

use Doofinder\Feed\Api\Data\ChangedProductSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class ChangedProductSearchResults extends SearchResults implements ChangedProductSearchResultsInterface
{

}
