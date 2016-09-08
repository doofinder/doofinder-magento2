<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map\Product;

class AssociateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product\Associate
     */
    protected $_model;

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

        $this->_helper = $this->getMock(
            '\Doofinder\Feed\Helper\Product',
            [],
            [],
            '',
            false
        );

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );
        $this->_productDataValue = 'sample value';
        $this->_product->method('getData')->will($this->returnCallback(function () {
            return $this->_productDataValue;
        }));

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );
        $this->_item->method('getContext')->willReturn($this->_product);

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Map\Product\Associate',
            [
                'helper' => $this->_helper,
                'item' => $this->_item,
            ]
        );
    }

    /**
     * Test get() method
     *
     * @dataProvider testGetProvider
     */
    public function testGet($key, $hasValue)
    {
        $this->assertEquals(
            $hasValue ? 'sample value' : null,
            $this->_model->get($key)
        );
    }

    public function testGetProvider()
    {
        return [
            ['sample', true],
            ['df_id', false],
            ['name', false],
            ['description', false],
            ['price', false],
            ['image', false],
            ['availability', false],
            ['type_id', false],
        ];
    }

    /**
     * Test get() method
     */
    public function testGetUrlKey()
    {
        $this->_product->method('isVisibleInSiteVisibility')->will(
            $this->onConsecutiveCalls(true, false)
        );
        $this->_helper->method('getProductUrl')->with($this->_product)
            ->willReturn('http://example.com/path/to/product');

        $this->assertEquals(
            'http://example.com/path/to/product',
            $this->_model->get('url_key')
        );

        $this->assertEquals(
            null,
            $this->_model->get('url_key')
        );
    }
}
