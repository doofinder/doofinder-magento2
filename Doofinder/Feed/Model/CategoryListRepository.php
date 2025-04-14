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

/**
 * Custom implementation of Magento's CategoryListInterface
 * to enhance category data (e.g., full URLs).
 */
class CategoryListRepository implements \Magento\Catalog\Api\CategoryListInterface
{
    /**
     * @var \Magento\Catalog\Model\CategoryList
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected $categoryInterface;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    /**
     * CategoryListRepository constructor.
     *
     * @param CategoryList $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param CategoryInterface $categoryInterface
     */
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

    /**
     * Retrieves a list of categories matching the given search criteria.
     *
     * This implementation enhances each category by appending the full URL
     * (base URL + URL path + suffix) to the extension attributes.
     *
     * @param SearchCriteriaInterface $searchCriteria Search criteria for filtering categories.
     * @return \Magento\Catalog\Api\Data\CategorySearchResultsInterface
     */
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
