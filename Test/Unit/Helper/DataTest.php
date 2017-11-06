<?php

namespace Doofinder\Feed\Test\Unit\Helper;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class DataTest
 * @package Doofinder\Feed\Test\Unit\Helper
 */
class DataTest extends BaseTestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeConfig;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $_module;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $_helper;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_storeManager = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->_storeConfig = $this->getMock(
            'Magento\Store\Api\Data\StoreConfigInterface',
            [],
            [],
            '',
            false
        );

        $this->_module = $this->getMock(
            '\Magento\Framework\Module\ModuleListInterface',
            [],
            [],
            '',
            false
        );

        $this->_logger = $this->getMock(
            '\Psr\Log\LoggerInterface',
            [],
            [],
            '',
            false
        );

        $this->_helper = $this->objectManager->getObject(
            '\Doofinder\Feed\Helper\Data',
            [
                'storeManager'  => $this->_storeManager,
                'moduleList'    => $this->_module,
                'logger'        => $this->_logger,
            ]
        );
    }

    /**
     * Test getInteger() method.
     *
     * @dataProvider integerProvider
     */
    public function testGetInteger($param, $defaultValue, $expected)
    {
        $this->assertSame($expected, $this->_helper->getInteger($param, $defaultValue));
    }

    public function integerProvider()
    {
        return [
            [1, null, 1],
            [1, null, 1],
            ['string', null, null],
            [null, 1, 1]
        ];
    }

    /**
     * Test isBoolean() method.
     *
     * @dataProvider booleanProvider
     */
    public function testIsBoolean($value, $defaultValue, $expected)
    {
        $this->assertSame($expected, $this->_helper->isBoolean($value, $defaultValue));
    }

    public function booleanProvider()
    {
        return [
            [1, false, true],
            ['-2', true, false],
            ['true', false, true],
            ['yes', false, true],
            ['on', false, true],
            ['false', true, false],
            ['no', true, false],
            ['off', true, false],
            ['string', true, true],
            ['string', false, false],
            [null, false, false],
            [-1, true, false],
            [0, true, false],
            [2, false, true],
            [true, false, true],
            [false, true, true]
        ];
    }

    public function testGetBaseUrl()
    {
        $this->_storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeConfig);

        $this->_storeConfig->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('localhost'));

        $this->assertSame('localhost', $this->_helper->getBaseUrl());
    }

    public function testGetModuleVersion()
    {
        $moduleInfo = [
            'name' => 'Doofinder_Feed',
            'setup_version' => '0.1.0',
            'sequence' => []
        ];

        $this->_module->expects($this->once())
            ->method('getOne')
            ->with(\Doofinder\Feed\Helper\Data::MODULE_NAME)
            ->willReturn($moduleInfo);

        $this->assertSame($moduleInfo['setup_version'], $this->_helper->getModuleVersion());
    }
}
