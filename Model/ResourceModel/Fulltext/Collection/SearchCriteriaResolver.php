<?php

namespace Doofinder\Feed\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteria;

/**
 * Class SearchCriteriaResolver
 * Resolve specific attributes for search criteria.
 */
class SearchCriteriaResolver implements SearchCriteriaResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $builder;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var string
     */
    private $searchRequestName;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var array
     */
    private $orders;

    /**
     * @var integer
     */
    private $currentPage;

    /**
     * SearchCriteriaResolver constructor.
     * @param SearchCriteriaBuilder $builder
     * @param Collection $collection
     * @param string $searchRequestName
     * @param integer|null $currentPage
     * @param integer|null $size
     * @param array|null $orders
     */
    public function __construct(
        SearchCriteriaBuilder $builder,
        Collection $collection,
        string $searchRequestName,
        int $currentPage = null,
        int $size = null,
        array $orders = []
    ) {
        $this->builder = $builder;
        $this->collection = $collection;
        $this->searchRequestName = $searchRequestName;
        $this->currentPage = $currentPage;
        $this->size = $size;
        $this->orders = $orders;
    }

    /**
     * {@inheritDoc}
     * @return SearchCriteria
     */
    public function resolve(): SearchCriteria
    {
        if (!$this->size) {
            $this->size = (int) $this->collection->getSize();
        }
        if (!$this->currentPage) {
            $this->currentPage = (int) $this->collection->getCurPage();
        }

        $searchCriteria = $this->builder->create();
        $searchCriteria->setRequestName($this->searchRequestName);
        $searchCriteria->setSortOrders($this->orders);
        $searchCriteria->setCurrentPage($this->currentPage - 1);

        return $searchCriteria;
    }
}
