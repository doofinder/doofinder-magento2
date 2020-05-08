<?php

namespace Doofinder\Feed\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Class TotalRecordsResolver
 * Resolve total records count.
 */
class TotalRecordsResolver implements TotalRecordsResolverInterface
{
    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * @param SearchResultInterface $searchResult
     */
    public function __construct(
        SearchResultInterface $searchResult
    ) {
        $this->searchResult = $searchResult;
    }

    /**
     * @return integer|null
     */
    public function resolve(): int
    {
        return (int) $this->searchResult->getTotalCount();
    }
}
