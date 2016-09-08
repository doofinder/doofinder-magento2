<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MapFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\MapFactory
     */
    private $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = $this->getMock(
            '\Magento\Framework\ObjectManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->_model = $this->getMock(
            'Doofinder\Feed\Model\Generator\MapFactory',
            null,
            [
                'objectManager' => $this->_objectManager,
            ]
        );
    }

    /**
     * Test create
     *
     * @dataProvider testCreateProvider
     */
    public function testCreate($class, $type, $subclassExists, $expected)
    {
        // Create subclass mock
        if ($subclassExists) {
            $this->getMock($expected, [], [], '', false);
        }

        $context = $this->getMock(
            $class,
            [],
            [],
            '',
            false
        );
        $context->method('getTypeId')->willReturn($type);

        $item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );
        $item->method('getContext')->willReturn($context);

        $this->_objectManager->expects($this->once())->method('create')
            ->with($expected, ['item' => $item, 'data' => ['sample' => 'data']]);

        $this->_model->create($item, ['sample' => 'data']);
    }

    public function testCreateProvider()
    {
        return [
            [
                '\Magento\Framework\DataObject',
                null,
                false,
                '\Doofinder\Feed\Model\Generator\Map',
            ],
            [
                '\Magento\Catalog\Model\Product',
                 'simple',
                 true,
                 '\Doofinder\Feed\Model\Generator\Map\Product\Simple',
            ],
            [
                '\Magento\Catalog\Model\Product',
                 'configurable',
                 true,
                 '\Doofinder\Feed\Model\Generator\Map\Product\Configurable',
            ],
            [
                '\Magento\Catalog\Model\Product',
                 'configurable',
                 false,
                 '\Doofinder\Feed\Model\Generator\Map\Product\Configurable',
            ],
        ];
    }
}
