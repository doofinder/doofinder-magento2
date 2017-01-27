<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

/**
 * Class CronTest
 * @package Doofinder\Feed\Test\Unit\Model\Backend
 */
class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $_contextMock;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_configScope;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeListMock;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\Value
     */
    protected $_configValueMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_managerInterfaceMock;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\Cron
     */
    protected $_model;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_contextMock = $this->getMock(
            '\Magento\Framework\Model\Context',
            [],
            [],
            '',
            false
        );

        $this->_registryMock = $this->getMock(
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

        $this->_cacheTypeListMock = $this->getMock(
            '\Magento\Framework\App\Cache\TypeListInterface',
            [],
            [],
            '',
            false
        );

        $this->_configValueFactoryMock = $this->getMock(
            '\Magento\Framework\App\Config\ValueFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_configValueMock = $this->getMock(
            '\Magento\Framework\App\Config\Value',
            ['load', 'setValue', 'setPath', 'save'],
            [],
            '',
            false
        );

        $this->_managerInterfaceMock = $this->getMock(
            '\Magento\Framework\Event\ManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->_contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->_managerInterfaceMock);

        $this->_configValueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_configValueMock);

        $this->_configValueMock->expects($this->once())
            ->method('load')
            ->with(\Doofinder\Feed\Model\Config\Backend\Cron::CRON_STRING_PATH, 'path')
            ->willReturnSelf();

        $this->_configValueMock->expects($this->once())
            ->method('setValue')
            ->willReturnSelf();

        $this->_configValueMock->expects($this->once())
            ->method('setPath')
            ->with(\Doofinder\Feed\Model\Config\Backend\Cron::CRON_STRING_PATH)
            ->willReturnSelf();

        $this->_model = $this->_objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Backend\Cron',
            [
                'context' => $this->_contextMock,
                'registry' => $this->_registryMock,
                'config' => $this->_configScope,
                'cacheTypeList' => $this->_cacheTypeListMock,
                'configValueFactory' => $this->_configValueFactoryMock,
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