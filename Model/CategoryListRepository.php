<?php

namespace Doofinder\Feed\Model;

use Magento\Catalog\Model\CategoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;


class CategoryListRepository implements \Magento\Catalog\Api\CategoryListInterface
{
    protected $categoryRepository;
    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $scopeConfig;
    protected $storeManager;

    public function __construct(
        CategoryList $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult =  $this->categoryRepository->getList($searchCriteria);

        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $category_url_suffix = $this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        foreach ($searchResult->getItems() as $category) {
            $category->getData();
            $fullPath = $baseUrl . $category['url_path'] . $category_url_suffix;
            $category->setData("url_path", $fullPath);    
        }

        return $searchResult;
    }
}
