<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\Cron
 */
class CronTest extends BaseTestCase
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
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    private $configValueFactory;

    /**
     * @var \Magento\Framework\App\Config\Value
     */
    private $configValue;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $managerInterface;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\Cron
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

        $this->configValueFactory = $this->getMock(
            \Magento\Framework\App\Config\ValueFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->configValue = $this->getMock(
            \Magento\Framework\App\Config\Value::class,
            ['load', 'setValue', 'setPath', 'save'],
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

        $this->configValueFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->configValue);

        $this->configValue->expects($this->once())
            ->method('load')
            ->with(\Doofinder\Feed\Model\Config\Backend\Cron::CRON_STRING_PATH, 'path')
            ->willReturnSelf();

        $this->configValue->expects($this->once())
            ->method('setValue')
            ->willReturnSelf();

        $this->configValue->expects($this->once())
            ->method('setPath')
            ->with(\Doofinder\Feed\Model\Config\Backend\Cron::CRON_STRING_PATH)
            ->willReturnSelf();

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\Cron::class,
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'config' => $this->configScope,
                'cacheTypeList' => $this->cacheTypeList,
                'configValueFactory' => $this->configValueFactory,
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
