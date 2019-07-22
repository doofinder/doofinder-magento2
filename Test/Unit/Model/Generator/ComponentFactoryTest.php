<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

/**
 * Test class for \Doofinder\Feed\Model\Generator\ComponentFactory
 */
class ComponentFactoryTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\ComponentFactory
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

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->getMockForAbstractClass(
            \Doofinder\Feed\Model\Generator\ComponentFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'instanceName' => \Test\Unit\Doofinder\Feed::class
            ]
        );
    }

    /**
     * Test create() method
     *
     * @return void
     */
    public function testCreate()
    {
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(\Test\Unit\Doofinder\Feed\Test::class, ['sample' => 'data']);

        $this->model->create(['sample' => 'data'], 'Test');
    }
}
