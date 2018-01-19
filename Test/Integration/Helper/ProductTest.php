<?php

namespace Doofinder\Feed\Test\Integration\Helper;

use Magento\TestFramework\TestCase\AbstractIntegrity;

/**
 * Test class for \Doofinder\Feed\Helper\Product
 */
class ProductTest extends AbstractIntegrity
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
    public function setUp()
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

        $this->assertRegExp(
            '/https?:\/\/[^\/]+(:\d+)?\/(.*\/)?simple-product\.html/',
            $this->helper->getProductUrl($product)
        );
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

        $this->assertEquals(
            [
                3 => [
                    3 => '1/2/3',
                ],
                4 => [
                    3 => '1/2/3',
                    4 => '1/2/3/4',
                ],
                13 => [
                    3 => '1/2/3',
                    13 => '1/2/3/13',
                ],
            ],
            array_map(
                function ($element) {
                    return array_map(
                        function ($category) {
                            return $category->getPath();
                        },
                        $element
                    );
                },
                $this->helper->getProductCategoriesWithParents($product)
            )
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
        $category = $this->categoryRepository->get(3);
        $category->setIncludeInMenu(0);
        $category->save();

        $this->assertEquals(
            [
                4 => [
                    4 => '1/2/3/4',
                ],
                13 => [
                    13 => '1/2/3/13',
                ],
            ],
            array_map(
                function ($element) {
                    return array_map(
                        function ($category) {
                            return $category->getPath();
                        },
                        $element
                    );
                },
                $this->helper->getProductCategoriesWithParents($product, true)
            )
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

        $this->assertRegExp(
            // @codingStandardsIgnoreStart
            '/https?:\/\/[^\/]+(:\d+)?\/(.*\/)?pub\/media\/catalog\/product\/cache\/((\d+?\/)?image\/)?[^\/]+\/\w\/\w\/magento_image\.jpg/',
            // @codingStandardsIgnoreEnd
            $this->helper->getProductImageUrl($product)
        );
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
            null,
            $this->helper->getAttributeText($product, 'tax_class_id')
        );

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
