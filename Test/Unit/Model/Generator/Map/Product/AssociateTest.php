<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map\Product;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

class AssociateTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product\Associate
     */
    private $_model;

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
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_helper = $this->getMock(
            '\Doofinder\Feed\Helper\Product',
            [],
            [],
            '',
            false
        );
        $this->_helper->method('getAttributeText')->willReturn('sample value');

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );
        $this->_item->method('getContext')->willReturn($this->_product);

        $this->_model = $this->objectManager->getObject(
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
