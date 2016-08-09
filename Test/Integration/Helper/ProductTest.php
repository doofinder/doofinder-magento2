<?php

namespace Doofinder\Feed\Test\Integration\Helper;

/**
 * Test class for \Doofinder\Feed\Helper\Product
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    private $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $_productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $_categoryRepository;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_productRepository = $this->_objectManager
            ->create('\Magento\Catalog\Api\ProductRepositoryInterface');

        $this->_categoryRepository = $this->_objectManager
            ->create('\Magento\Catalog\Api\CategoryRepositoryInterface');

        $this->_helper = $this->_objectManager->create(
            '\Doofinder\Feed\Helper\Product'
        );
    }

    /**
     * Test getProductUrl
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductUrl()
    {
        $product = $this->_productRepository->get('simple');

        $this->assertRegExp(
            '/https?:\/\/[^\/]+(:\d+)?\/(.*\/)?simple-product\.html/',
            $this->_helper->getProductUrl($product)
        );
    }

    /**
     * Test getProductCategoriesWithParents
     *
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductCategoriesWithParents()
    {
        $product = $this->_productRepository->get('simple');

        $this->assertEquals(
            [
                [
                    2 => '1/2',
                ],
                [
                    3 => '1/2/3',
                ],
                [
                    3 => '1/2/3',
                    4 => '1/2/3/4',
                ],
                [
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
                $this->_helper->getProductCategoriesWithParents($product)
            )
        );
    }

    /**
     * Test getProductImageUrl
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductImageUrl()
    {
        $product = $this->_productRepository->get('simple');

        $this->assertRegExp(
            // @codingStandardsIgnoreStart
            '/https?:\/\/[^\/]+(:\d+)?\/(.*\/)?pub\/media\/catalog\/product\/cache\/\d+\/image\/[^\/]+\/\w\/\w\/magento_image\.jpg/',
            // @codingStandardsIgnoreEnd
            $this->_helper->getProductImageUrl($product)
        );
    }

    /**
     * Test getProductPrice
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductPrice()
    {
        $product = $this->_productRepository->get('simple');

        $this->assertEquals(
            10,
            $this->_helper->getProductPrice($product)
        );
    }
}
