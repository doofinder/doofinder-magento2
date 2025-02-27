<?php

namespace Doofinder\Feed\Model;

use Magento\Catalog\Model\CategoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
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
    protected $categoryInterface;
    protected $categoryRepositoryInterface;

    public function __construct(
        CategoryList $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        CategoryInterface $categoryInterface
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->categoryInterface = $categoryInterface;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
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
            $categoryData = $category->getData();
            $fullPath = $baseUrl . $categoryData['url_path'] . $category_url_suffix;
            $extensionAttributes = $category->getExtensionAttributes();
            $extensionAttributes->setUrlFull($fullPath);
        }
    
        return $searchResult;
    }
}
