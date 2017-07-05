<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class CronTest
 * @package Doofinder\Feed\Test\Unit\Model\Backend
 */
class CronTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\Model\Context
     */
    private $_context;

    /**
     * @var \Magento\Framework\Registry
     */
    private $_registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_configScope;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    private $_configValueFactory;

    /**
     * @var \Magento\Framework\App\Config\Value
     */
    private $_configValue;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_managerInterface;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\Cron
     */
    private $_model;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_context = $this->getMock(
            '\Magento\Framework\Model\Context',
            [],
            [],
            '',
            false
        );

        $this->_registry = $this->getMock(
            '\Magento\Framework\Registry',
            [],
            [],
            '',
            false
        );

        $this->_configScope = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $this->_cacheTypeList = $this->getMock(
            '\Magento\Framework\App\Cache\TypeListInterface',
            [],
            [],
            '',
            false
        );

        $this->_configValueFactory = $this->getMock(
            '\Magento\Framework\App\Config\ValueFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_configValue = $this->getMock(
            '\Magento\Framework\App\Config\Value',
            ['load', 'setValue', 'setPath', 'save'],
            [],
            '',
            false
        );

        $this->_managerInterface = $this->getMock(
            '\Magento\Framework\Event\ManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->_context->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->_managerInterface);

        $this->_configValueFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->_configValue);

        $this->_configValue->expects($this->once())
            ->method('load')
            ->with(\Doofinder\Feed\Model\Config\Backend\Cron::CRON_STRING_PATH, 'path')
            ->willReturnSelf();

        $this->_configValue->expects($this->once())
            ->method('setValue')
            ->willReturnSelf();

        $this->_configValue->expects($this->once())
            ->method('setPath')
            ->with(\Doofinder\Feed\Model\Config\Backend\Cron::CRON_STRING_PATH)
            ->willReturnSelf();

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Backend\Cron',
            [
                'context' => $this->_context,
                'registry' => $this->_registry,
                'config' => $this->_configScope,
                'cacheTypeList' => $this->_cacheTypeList,
                'configValueFactory' => $this->_configValueFactory,
            ]
        );
    }

    /**
     * Test afterSave()
     */
    public function testAfterSave()
    {
        $this->_model->afterSave();
    }
}
