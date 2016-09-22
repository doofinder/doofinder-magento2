<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $_category;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_product;

    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    private $_helper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_category = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            '',
            false
        );
        $this->_category->method('getName')->will(
            $this->onConsecutiveCalls(
                'Category 1',
                'Category 1.1',
                'Category 2'
            )
        );

        $this->_helper = $this->getMock(
            '\Doofinder\Feed\Helper\Product',
            [],
            [],
            '',
            false
        );
        $this->_helper->method('getProductUrl')->willReturn('http://example.com/simple-product.html');
        $this->_helper->method('getProductCategoriesWithParents')->willReturn([
            [
                $this->_category,
                $this->_category,
            ],
            [
                $this->_category,
            ]
        ]);
        $this->_helper->method('getProductImageUrl')->willReturn('http://example.com/path/to/image.jpg');
        $this->_helper->method('getProductPrice')->willReturn(10.1234);
        $this->_helper->method('getProductAvailability')->willReturn('in stock');
        $this->_helper->method('getCurrencyCode')->willReturn('USD');
        $this->_helper->method('getAttributeText')->will($this->onConsecutiveCalls('blue', 'Taxable', 'Company'));
        $this->_helper->method('getQuantityAndStockStatus')->willReturn('5 - in stock');

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );
        $map = [
            ['title', null, 'Sample title',],
            ['description', null, 'Sample description',],
        ];
        $this->_product->method('getData')->will($this->returnValueMap($map));

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );
        $this->_item->method('getContext')->willReturn($this->_product);

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Map\Product',
            [
                'helper' => $this->_helper,
                'item' => $this->_item,
            ]
        );
        $this->_model->setExportProductPrices(true);
    }

    /**
     * Test get() method
     */
    public function testGet()
    {
        $this->assertEquals('Sample title', $this->_model->get('title'));
        $this->assertEquals('Sample description', $this->_model->get('description'));
        $this->assertEquals('Category 1>Category 1.1%%Category 2', $this->_model->get('category_ids'));
        $this->assertEquals('http://example.com/path/to/image.jpg', $this->_model->get('image'));
        $this->assertEquals('http://example.com/simple-product.html', $this->_model->get('url_key'));
        $this->assertEquals('10.12', $this->_model->get('price'));
        $this->assertEquals(null, $this->_model->setExportProductPrices(false)->get('price'));
        $this->assertEquals('in stock', $this->_model->get('df_availability'));
        $this->assertEquals('USD', $this->_model->get('currency'));
        $this->assertEquals('blue', $this->_model->get('color'));
        $this->assertEquals('Taxable', $this->_model->get('tax_class_id'));
        $this->assertEquals('Company', $this->_model->get('manufacturer'));
        $this->assertEquals('5 - in stock', $this->_model->get('quantity_and_stock_status'));
    }
}
