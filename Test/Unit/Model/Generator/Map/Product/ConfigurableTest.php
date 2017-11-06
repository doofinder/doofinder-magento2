<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map\Product;

use Doofinder\Feed\Test\Unit\BaseTestCase;

class ConfigurableTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product\Configurable
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
        $this->_productAttributeValue = 'sample parent value';
        $this->_helper->method('getAttributeText')->will($this->returnCallback(function () {
            return $this->_productAttributeValue;
        }));

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

        $this->_associate = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
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
        $this->_item->method('getAssociates')->willReturn([
            $this->_associate,
            $this->_associate,
            $this->_associate,
            $this->_associate,
        ]);

        $this->_map = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Map\Product\Associate',
            [],
            [],
            '',
            false
        );
        $this->_map->method('get')->will($this->onConsecutiveCalls(
            'sample associate value 1',
            'sample associate value 2',
            null,
            'sample associate value 2'
        ));

        $this->_mapFactory = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Map\Product\AssociateFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_mapFactory->method('create')->willReturn($this->_map);

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Generator\Map\Product\Configurable',
            [
                'mapFactory' => $this->_mapFactory,
                'helper' => $this->_helper,
                'item' => $this->_item,
            ]
        );
    }

    /**
     * Test before() method with split option
     */
    public function testBeforeSplit()
    {
        $this->_associate->expects($this->never())->method('skip');

        $this->_model->setSplitConfigurableProducts(true);
        $this->_model->before();
    }

    /**
     * Test before() method without split option
     */
    public function testBeforeNoSplit()
    {
        $this->_associate->expects($this->exactly(4))->method('skip');

        $this->_model->setSplitConfigurableProducts(false);
        $this->_model->before();
    }

    /**
     * Test get() method with split option
     */
    public function testGetSplit()
    {
        $this->_model->setSplitConfigurableProducts(true);
        $this->_model->before();

        $this->assertEquals(
            'sample parent value',
            $this->_model->get('sample')
        );
    }

    /**
     * Test get() method without split option
     */
    public function testGetNoSplit()
    {
        $this->_model->setSplitConfigurableProducts(false);
        $this->_model->before();

        $this->assertEquals(
            [
                'sample parent value',
                'sample associate value 1',
                'sample associate value 2',
            ],
            $this->_model->get('sample')
        );
    }

    /**
     * Test get() method without split option, when parents value is array
     */
    public function testGetNoSplitParentArray()
    {
        $this->_productAttributeValue = ['sample parent value 1', 'sample parent value 2'];

        $this->_model->setSplitConfigurableProducts(false);
        $this->_model->before();

        $this->assertEquals(
            [
                'sample parent value 1',
                'sample parent value 2',
                'sample associate value 1',
                'sample associate value 2',
            ],
            $this->_model->get('sample')
        );
    }
}
