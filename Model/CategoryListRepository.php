<?php

namespace Doofinder\Module\Model;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;

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

    public function execute()
    {
        $entityIdFilter = $this->filterBuilder->setField('entity_id')->setValue(1)->setConditionType('gt')->create();
        $parentIdFilter = $this->filterBuilder->setField('parent_id')->setValue(1)->setConditionType('gt')->create();
        $isActiveFilter = $this->filterBuilder->setField('is_active')->setValue(1)->setConditionType('eq')->create();

        $filterGroup = new FilterGroup();
        $filterGroup->setFilters([$entityIdFilter, $parentIdFilter, $isActiveFilter]);

        $searchCriteria = $this->searchCriteriaBuilder->setFilterGroups([$filterGroup])->create();

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
