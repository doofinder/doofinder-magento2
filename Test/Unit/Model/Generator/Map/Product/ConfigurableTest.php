<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map\Product;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Map\Product\Configurable
 */
class ConfigurableTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product\Configurable
     */
    private $model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

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

        $this->helper = $this->getMock(
            \Doofinder\Feed\Helper\Product::class,
            [],
            [],
            '',
            false
        );
        $this->productAttributeValue = 'sample parent value';
        $this->helper->method('getAttributeText')->will($this->returnCallback(function () {
            return $this->productAttributeValue;
        }));

        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [],
            [],
            '',
            false
        );

        $this->associate = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );
        $this->item->method('getContext')->willReturn($this->product);
        $this->item->method('getAssociates')->willReturn([
            $this->associate,
            $this->associate,
            $this->associate,
            $this->associate,
        ]);

        $this->map = $this->getMock(
            \Doofinder\Feed\Model\Generator\Map\Product\Associate::class,
            [],
            [],
            '',
            false
        );
        $this->map->method('get')->will($this->onConsecutiveCalls(
            'sample associate value 1',
            'sample associate value 2',
            null,
            'sample associate value 2'
        ));

        $this->mapFactory = $this->getMock(
            \Doofinder\Feed\Model\Generator\Map\Product\AssociateFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->mapFactory->method('create')->willReturn($this->map);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Map\Product\Configurable::class,
            [
                'mapFactory' => $this->mapFactory,
                'helper' => $this->helper,
                'item' => $this->item,
            ]
        );
    }

    /**
     * Test before() method with split option
     *
     * @return void
     */
    public function testBeforeSplit()
    {
        $this->associate->expects($this->never())->method('skip');

        $this->model->setSplitConfigurableProducts(true);
        $this->model->before();
    }

    /**
     * Test before() method without split option
     *
     * @return void
     */
    public function testBeforeNoSplit()
    {
        $this->associate->expects($this->exactly(4))->method('skip');

        $this->model->setSplitConfigurableProducts(false);
        $this->model->before();
    }

    /**
     * Test get() method with split option
     *
     * @return void
     */
    public function testGetSplit()
    {
        $this->model->setSplitConfigurableProducts(true);
        $this->model->before();

        $this->assertEquals(
            'sample parent value',
            $this->model->get('sample')
        );
    }

    /**
     * Test get() method without split option
     *
     * @return void
     */
    public function testGetNoSplit()
    {
        $this->model->setSplitConfigurableProducts(false);
        $this->model->before();

        $this->assertEquals(
            [
                'sample parent value',
                'sample associate value 1',
                'sample associate value 2',
            ],
            $this->model->get('sample')
        );
    }

    /**
     * Test get() method without split option, when parents value is array
     *
     * @return void
     */
    public function testGetNoSplitParentArray()
    {
        $this->productAttributeValue = ['sample parent value 1', 'sample parent value 2'];

        $this->model->setSplitConfigurableProducts(false);
        $this->model->before();

        $this->assertEquals(
            [
                'sample parent value 1',
                'sample parent value 2',
                'sample associate value 1',
                'sample associate value 2',
            ],
            $this->model->get('sample')
        );
    }
}
