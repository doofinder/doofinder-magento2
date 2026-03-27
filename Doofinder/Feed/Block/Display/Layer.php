<?php
declare(strict_types=1);


namespace Doofinder\Feed\Block\Display;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Layer extends Template
{
    private const PAGE_TYPE_MAP = [
        'cms_index_index' => 'home',
        'catalog_product_view' => 'product',
        'catalog_category_view' => 'category',
        'catalogsearch_result_index' => 'search',
        'checkout_cart_index' => 'cart',
        'checkout_index_index' => 'checkout',
    ];

    /** @var StoreConfig */
    private $storeConfig;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryCollectionFactory */
    private $categoryCollectionFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param StoreConfig $storeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Template\Context $context
     * @param mixed[] $data
     */
    public function __construct(
        StoreConfig $storeConfig,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        Template\Context $context,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return string|null
     */
    public function getDisplayLayer(): ?string
    {
        return $this->storeConfig->getDisplayLayer();
    }

    /**
     * @return string
     */
    public function getPageType(): string
    {
        $fullActionName = $this->getRequest()->getFullActionName();
        return self::PAGE_TYPE_MAP[$fullActionName] ?? 'other';
    }

    /**
     * @return string
     */
    public function getDfProductId(): string
    {
        if ($this->getPageType() !== 'product') {
            return '';
        }

        $productId = $this->getRequest()->getParam('id');
        return $productId ? (string) $productId : '';
    }

    /**
     * @return string
     */
    public function getDfCategoryName(): string
    {
        $pageType = $this->getPageType();

        if ($pageType === 'category') {
            return $this->getCategoryBreadcrumbById((int) $this->getRequest()->getParam('id')) ?? '';
        }

        if ($pageType === 'product') {
            return $this->getCategoryBreadcrumbById((int) $this->getRequest()->getParam('category'))
                ?? $this->getDeepestCategoryBreadcrumbForProduct((int) $this->getRequest()->getParam('id'))
                ?? '';
        }

        return '';
    }

    /**
     * @param int $categoryId
     * @return string|null
     */
    private function getCategoryBreadcrumbById(int $categoryId): ?string
    {
        if ($categoryId <= 0) {
            return null;
        }
        try {
            $category = $this->categoryRepository->get($categoryId);
            return $this->buildCategoryBreadcrumb($category->getPath()) ?: null;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param int $productId
     * @return string|null
     */
    private function getDeepestCategoryBreadcrumbForProduct(int $productId): ?string
    {
        if ($productId <= 0) {
            return null;
        }
        try {
            $product = $this->productRepository->getById($productId);
            $categoryIds = $product->getCategoryIds();
            if (empty($categoryIds)) {
                return null;
            }
            $collection = $this->categoryCollectionFactory->create();
            $collection->addIdFilter($categoryIds)
                ->addAttributeToSelect('path')
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToSort('level', 'DESC')
                ->setPageSize(1);

            $deepestCategory = $collection->getFirstItem();
            if ($deepestCategory && $deepestCategory->getId()) {
                return $this->buildCategoryBreadcrumb($deepestCategory->getPath()) ?: null;
            }
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getNonceAttribute(): string
    {
        if (class_exists(\Magento\Csp\Helper\CspNonceProvider::class)) {
            $cspNonceProvider = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Csp\Helper\CspNonceProvider::class);
            return ' nonce="' . $cspNonceProvider->generateNonce() . '"';
        }
        return '';
    }

    /**
     * Build a category breadcrumb string from a category path,
     * using the same store-root scoping logic as the indexer.
     *
     * @param string $categoryPath e.g. "1/2/5/12/34"
     * @return string e.g. "Electronics > Computers > Laptops"
     */
    private function buildCategoryBreadcrumb(string $categoryPath): string
    {
        try {
            $storeRootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        } catch (\Exception $e) {
            return '';
        }

        $storeRootPath = '1/' . $storeRootCategoryId . '/';
        $scopedPath = str_replace($storeRootPath, '', $categoryPath);
        $categoryIds = array_filter(explode('/', $scopedPath), function ($id) {
            return $id !== '' && $id !== '1' && $id !== (string) 0;
        });

        if (empty($categoryIds)) {
            return '';
        }

        $collection = $this->categoryCollectionFactory->create();
        $collection->addIdFilter($categoryIds)
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', 1);

        $namesById = [];
        foreach ($collection as $cat) {
            $namesById[(int) $cat->getId()] = $cat->getName();
        }

        $orderedNames = [];
        foreach ($categoryIds as $id) {
            if (isset($namesById[(int) $id])) {
                $orderedNames[] = $namesById[(int) $id];
            }
        }

        return implode(' > ', $orderedNames);
    }
}
