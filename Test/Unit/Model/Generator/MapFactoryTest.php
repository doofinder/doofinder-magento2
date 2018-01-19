<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

use Doofinder\Feed\Test\Unit\BaseTestCase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Doofinder\Feed\Model\Generator\MapFactory
 */
class MapFactoryTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\MapFactory
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManagerMock;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectManagerMock = $this->getMock(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            [],
            '',
            false
        );

        $this->model = $this->getMock(
            \Doofinder\Feed\Model\Generator\MapFactory::class,
            null,
            [
                'objectManager' => $this->objectManagerMock,
            ]
        );
    }

    /**
     * Test create
     *
     * @param  string $class
     * @param  string $type
     * @param  boolean $subclassExists
     * @param  string $expected
     * @return void
     * @dataProvider providerTestCreate
     */
    public function testCreate($class, $type, $subclassExists, $expected)
    {
        // Create subclass mock
        if ($subclassExists) {
            $this->getMock($expected, [], [], '', false);
        }

        $context = $this->getMock(
            $class,
            ['getTypeId'],
            [],
            '',
            false
        );
        $context->method('getTypeId')->willReturn($type);

        $item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );
        $item->method('getContext')->willReturn($context);

        $this->objectManagerMock->expects($this->once())->method('create')
            ->with($expected, ['item' => $item, 'data' => ['sample' => 'data']]);

        $this->model->create($item, ['sample' => 'data']);
    }

    /**
     * Data provider for testCreate() test
     *
     * @return array
     */
    public function providerTestCreate()
    {
        return [
            [
                \Magento\Framework\DataObject::class,
                null,
                false,
                \Doofinder\Feed\Model\Generator\Map::class,
            ],
            [
                \Magento\Catalog\Model\Product::class,
                 'simple',
                 true,
                 \Doofinder\Feed\Model\Generator\Map\Product\Simple::class,
            ],
            [
                \Magento\Catalog\Model\Product::class,
                 'configurable',
                 true,
                 \Doofinder\Feed\Model\Generator\Map\Product\Configurable::class,
            ],
            [
                \Magento\Catalog\Model\Product::class,
                 'configurable',
                 false,
                 \Doofinder\Feed\Model\Generator\Map\Product\Configurable::class,
            ],
        ];
    }
}
