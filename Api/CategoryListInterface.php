<?php

namespace Doofinder\Feed\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Catalog\Api\Data\CategoryProductSearchResultInterface;

interface CategoryListInterface
{
    /**
     * Get category list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\CategorySearchResultsInterface
     * @since 102.0.0
     */
    public function getList(SearchCriteriaInterface $searchCriteria): CategoryProductSearchResultInterface;
}
