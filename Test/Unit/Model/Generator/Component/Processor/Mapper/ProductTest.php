<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor\Mapper;

class ProductTest extends \Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor\MapperTest
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Mapper\Product
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $_category;

    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    private $_helper;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

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

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Mapper\Product',
            [
                'helper' => $this->_helper,
                'data' => [
                    'map' => [
                        'name' => 'title',
                        'desc' => 'description',
                        'categories' => 'category_ids',
                        'image_link' => 'image',
                        'url' => 'url_key',
                        'price' => 'price',
                    ]
                ]
            ]
        );
    }

    /**
     * Test process
     */
    public function testProcess()
    {
        $this->_model->process([$this->_item]);

        $this->assertEquals(
            [
                'name' => 'Sample title',
                'desc' => 'Sample description',
                'categories' => 'Category 1 > Category 1.1 %% Category 2',
                'image_link' => 'http://example.com/path/to/image.jpg',
                'url' => 'http://example.com/simple-product.html',
                'price' => '10.12',
            ],
            $this->_item->getData()
        );
    }
}
