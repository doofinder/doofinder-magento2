<?php

namespace Doofinder\Module\Model;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;

class CategoryListRepository
{
    protected $categoryRepository;
    protected $searchCriteriaBuilder;
    protected $filterBuilder;

    public function __construct(
        CategoryListInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $categories = $this->categoryRepository->getList($searchCriteria)->getItems();

        $categoryData = [];
        foreach ($categories as $category) {
            $categoryData[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'is_active' => $category->getIsActive(),
                'parent_id' => $category->getParentId(),
                'position' => $category->getPosition(),
                'level' => $category->getLevel(),
            ];
        }

        return $categoryData;
    }
}
