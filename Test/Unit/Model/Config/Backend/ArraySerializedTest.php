<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\ArraySerialized
 */
class ArraySerializedTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\Model\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configScope;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $managerInterface;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\ArraySerialized
     */
    private $model;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock(
            \Magento\Framework\Model\Context::class,
            [],
            [],
            '',
            false
        );

        $this->registry = $this->getMock(
            \Magento\Framework\Registry::class,
            [],
            [],
            '',
            false
        );

        $this->configScope = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            [],
            '',
            false
        );

        $this->cacheTypeList = $this->getMock(
            \Magento\Framework\App\Cache\TypeListInterface::class,
            [],
            [],
            '',
            false
        );

        $this->managerInterface = $this->getMock(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            [],
            '',
            false
        );

        $this->context->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->managerInterface);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\ArraySerialized::class,
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'config' => $this->configScope,
                'cacheTypeList' => $this->cacheTypeList,
            ]
        );
    }

    /**
     * Test afterSave() method
     *
     * @return void
     */
    public function testAfterSave()
    {
        $this->model->afterSave();
    }
}
