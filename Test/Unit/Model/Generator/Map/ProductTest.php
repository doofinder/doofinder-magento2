<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Map\Product
 */
class ProductTest extends BaseTestCase
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
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

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
     * @var \Doofinder\Feed\Helper\Product
     */
    private $helper;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->category = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [],
            [],
            '',
            false
        );
        $this->category->method('getName')->will(
            $this->onConsecutiveCalls(
                'Category 1',
                'Category 1.1',
                'Category 2'
            )
        );

        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [],
            [],
            '',
            false
        );

        $this->currency = $this->getMock(
            \Magento\Directory\Model\Currency::class,
            [],
            [],
            '',
            false
        );
        $this->currency->method('format')->with(10.1234)->willReturn('10.1234');

        $this->priceCurrency = $this->getMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class,
            [],
            [],
            '',
            false
        );
        $this->priceCurrency->method('getCurrency')->willReturn($this->currency);

        $this->helper = $this->getMock(
            \Doofinder\Feed\Helper\Product::class,
            [],
            [],
            '',
            false
        );
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

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );
        $this->item->method('getContext')->willReturn($this->product);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Map\Product::class,
            [
                'helper' => $this->helper,
                'item' => $this->item,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $this->model->setExportProductPrices(true);
    }

    /**
     * Test get() method
     *
     * @return void
     */
    public function testGet()
    {
        $this->assertEquals('Sample title', $this->model->get('title'));
        $this->assertEquals('Sample description', $this->model->get('description'));
        $this->assertEquals('Category 1>Category 1.1%%Category 2', $this->model->get('category_ids'));
        $this->assertEquals('http://example.com/path/to/image.jpg', $this->model->get('image'));
        $this->assertEquals('http://example.com/simple-product.html', $this->model->get('url_key'));
        $this->assertEquals('10.1234', $this->model->get('price'));
        $this->assertEquals(null, $this->model->setExportProductPrices(false)->get('price'));
        $this->assertEquals('in stock', $this->model->get('df_availability'));
        $this->assertEquals('USD', $this->model->get('df_currency'));
        $this->assertEquals('blue', $this->model->get('color'));
        $this->assertEquals('Taxable', $this->model->get('tax_class_id'));
        $this->assertEquals('Company', $this->model->get('manufacturer'));
        $this->assertEquals('5 - in stock', $this->model->get('quantity_and_stock_status'));
    }
}
