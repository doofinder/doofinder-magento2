<?php

namespace Doofinder\Feed\Model;

use Magento\Catalog\Model\CategoryList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;


class CategoryListRepository implements \Magento\Catalog\Api\CategoryListInterface
{
    protected $categoryRepository;
    protected $searchCriteriaBuilder;
    protected $filterBuilder;

    public function __construct(
        CategoryList $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return  $this->categoryRepository->getList($searchCriteria);
    }
}
