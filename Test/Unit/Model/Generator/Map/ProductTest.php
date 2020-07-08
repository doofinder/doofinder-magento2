<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Map\Product
 */
class ProductTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    private $helper;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->category->method('getName')->will(
            $this->onConsecutiveCalls(
                'Category 1',
                'Category 1.1',
                'Category 2'
            )
        );

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->currency->method('format')->with(10.1234)->willReturn('10.1234');

        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency->method('getCurrency')->willReturn($this->currency);

        $this->storeConfig = $this->getMockBuilder(\Doofinder\Feed\Helper\StoreConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeConfig->method('isExportProductPrices')->willReturn(true);

        $this->helper = $this->getMockBuilder(\Doofinder\Feed\Helper\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper->method('getProductUrl')->willReturn('http://example.com/simple-product.html');
        $this->helper->method('getProductCategoriesWithParents')->willReturn([
            [
                $this->category,
                $this->category,
            ],
            [
                $this->category,
            ]
        ]);
        $this->helper->method('getProductImageUrl')->willReturn('http://example.com/path/to/image.jpg');
        $this->helper->method('getProductPrice')->willReturn(10.1234);
        $this->helper->method('getProductAvailability')->willReturn('in stock');
        $this->helper->method('getCurrencyCode')->willReturn('USD');
        $this->helper->method('getQuantityAndStockStatus')->willReturn('5 - in stock');
        $map = [
            [$this->product, 'title', 'Sample title',],
            [$this->product, 'description', 'Sample description',],
            [$this->product, 'color', 'blue'],
            [$this->product, 'tax_class_id', 'Taxable'],
            [$this->product, 'manufacturer', 'Company'],
        ];
        $this->helper->method('getAttributeText')->will($this->returnValueMap($map));

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Map\Product::class,
            [
                'helper' => $this->helper,
                'priceCurrency' => $this->priceCurrency,
                'storeConfig' => $this->storeConfig,
            ]
        );
    }

    /**
     * Test get() method
     *
     * @return void
     */
    public function testGet()
    {
        $this->assertEquals('Sample title', $this->model->get($this->product, 'title'));
        $this->assertEquals('Sample description', $this->model->get($this->product, 'description'));
        $this->assertEquals(
            ['Category 1>Category 1.1', 'Category 2'],
            $this->model->get($this->product, 'category_ids')
        );
        $this->assertEquals(
            'http://example.com/path/to/image.jpg',
            $this->model->get($this->product, 'image')
        );
        $this->assertEquals(
            'http://example.com/simple-product.html',
            $this->model->get($this->product, 'url_key')
        );
        $this->assertEquals('10.1234', $this->model->get($this->product, 'price'));
        $this->assertEquals('in stock', $this->model->get($this->product, 'df_availability'));
        $this->assertEquals('USD', $this->model->get($this->product, 'df_currency'));
        $this->assertEquals('blue', $this->model->get($this->product, 'color'));
        $this->assertEquals('Taxable', $this->model->get($this->product, 'tax_class_id'));
        $this->assertEquals('Company', $this->model->get($this->product, 'manufacturer'));
        $this->assertEquals(
            '5 - in stock',
            $this->model->get($this->product, 'quantity_and_stock_status')
        );
    }
}
