<?php

namespace Doofinder\Feed\Test\Integration\Helper;

use Doofinder\FeedCompatibility\Test\Integration\Base;

/**
 * Test class for \Doofinder\Feed\Helper\Product
 */
class ProductTest extends Base
{
    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    private $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->productRepository = $this->objectManager
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $this->categoryRepository = $this->objectManager
            ->create(\Magento\Catalog\Api\CategoryRepositoryInterface::class);

        $this->helper = $this->objectManager->create(
            \Doofinder\Feed\Helper\Product::class
        );
    }

    /**
     * Test getProductId() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductId()
    {
        $product = $this->productRepository->get('simple');

        $this->assertEquals(
            1,
            $this->helper->getProductId($product)
        );
    }

    /**
     * Test getProductUrl() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductUrl()
    {
        $product = $this->productRepository->get('simple');

        $this->assertStringEndsWith('simple-product.html', $this->helper->getProductUrl($product));
    }

    /**
     * Test getProductCategoriesWithParents() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductCategoriesWithParents()
    {
        $product = $this->productRepository->get('simple');

        $categoriesTree = $this->helper->getProductCategoriesWithParents($product);

        $tree = [];
        foreach ($categoriesTree as $key => $categoryTree) {
            $ids = [];
            foreach ($categoryTree as $category) {
                $ids[] = $category->getId();
            }
            $tree[$key] = implode('/', $ids);
        }

        $this->assertEquals(
            [
                0 => '3/4',
                1 => '3/13'
            ],
            $tree
        );
    }

    /**
     * Test getProductCategoriesWithParentsInNavigation() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetProductCategoriesWithParentsNavigation()
    {
        $product = $this->productRepository->get('simple');
        $category = $this->categoryRepository->get(13);
        $category->setIncludeInMenu(0);
        $category->save();

        $categoriesTree = $this->helper->getProductCategoriesWithParents($product, true);

        $tree = [];
        foreach ($categoriesTree as $key => $categoryTree) {
            $ids = [];
            foreach ($categoryTree as $category) {
                $ids[] = $category->getId();
            }
            $tree[$key] = implode('/', $ids);
        }

        $this->assertEquals(
            [
                0 => '3/4'
            ],
            $tree
        );
    }

    /**
     * Test getProductImageUrl() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductImageUrl()
    {
        $product = $this->productRepository->get('simple');

        $this->assertStringEndsWith('magento_image.jpg', $this->helper->getProductImageUrl($product));
    }

    /**
     * Test getProductPrice() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductPrice()
    {
        $product = $this->productRepository->get('simple');

        $this->assertEquals(
            10,
            $this->helper->getProductPrice($product)
        );
    }

    /**
     * Test getProductAvailability() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetProductAvailability()
    {
        $product = $this->productRepository->get('simple');

        $this->assertEquals(
            'in stock',
            $this->helper->getProductAvailability($product)
        );
    }

    /**
     * Test getAttributeText() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetAttributeText()
    {
        $product = $this->productRepository->get('simple');

        $this->assertEquals(
            'Simple Product',
            $this->helper->getAttributeText($product, 'name')
        );

        $this->assertEquals(
            'Description with <b>html tag</b>',
            $this->helper->getAttributeText($product, 'description')
        );
    }

    /**
     * Test getQuantityAndStockStatus() method
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetQuantityAndStockStatus()
    {
        $product = $this->productRepository->get('simple');

        $this->assertEquals(
            '100 - in stock',
            $this->helper->getQuantityAndStockStatus($product)
        );
    }

    /**
     * Test getCurrencyCode() method
     *
     * @return void
     */
    public function testGetCurrencyCode()
    {
        $this->assertEquals(
            'USD',
            $this->helper->getCurrencyCode()
        );
    }
}
