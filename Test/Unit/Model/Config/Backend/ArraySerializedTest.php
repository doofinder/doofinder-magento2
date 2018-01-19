<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class ArraySerializedTest
 * @package Doofinder\Feed\Test\Unit\Model\Backend
 */
class ArraySerializedTest extends BaseTestCase
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
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_managerInterface;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\ArraySerialized
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

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Backend\ArraySerialized',
            [
                'context' => $this->_context,
                'registry' => $this->_registry,
                'config' => $this->_configScope,
                'cacheTypeList' => $this->_cacheTypeList,
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
