<?php

namespace Doofinder\Feed\Test\Unit\Helper\Data;

/**
 * Class DataTest
 * @package Doofinder\Feed\Test\Unit\Helper\Data
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeInterfaceMock;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagerMock;
    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $_storeInterfaceMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeConfigMock;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleMock;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    protected $_helper;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_scopeInterfaceMock = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $this->_storeManagerMock = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->_storeInterfaceMock = $this->getMock(
            '\Magento\Store\Api\Data\StoreInterface',
            [],
            [],
            '',
            false
        );

        $this->_storeConfigMock = $this->getMock(
            'Magento\Store\Api\Data\StoreConfigInterface',
            [],
            [],
            '',
            false
        );

        $this->_moduleMock = $this->getMock(
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

        $this->_helper = $this->_objectManager->getObject(
            '\Doofinder\Feed\Helper\Data',
            [
                'scopeConfig'   => $this->_scopeInterfaceMock,
                'storeManager'  => $this->_storeManagerMock,
                'moduleList'    => $this->_moduleMock,
                'logger'        => $this->_logger,
            ]
        );
    }

    /**
     * Test getStoreConfig() method.
     */
    public function testGetStoreConfig()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeInterfaceMock);

        $this->_storeInterfaceMock->expects($this->once())
            ->method('getCode')
            ->willReturn('default');

        $this->_scopeInterfaceMock->expects($this->at(0))
            ->method('getValue')
            ->with('doofinder_feed_feed/feed_settings/grouped')
            ->will($this->returnValue(0));

        $this->_scopeInterfaceMock->expects($this->at(1))
            ->method('getValue')
            ->with('doofinder_feed_feed/feed_settings/image_size')
            ->will($this->returnValue('100x100'));

        $expected = array(
            'grouped'       =>  0,
            'image_size'     => '100x100',
            'store_code'     => 'default',
        );

        $result = $this->_helper->getStoreConfig();

        $this->assertSame($result, $expected);
        $this->assertSame($expected, $result);
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
        $this->_storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeConfigMock);

        $this->_storeConfigMock->expects($this->once())
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

        $this->_moduleMock->expects($this->once())
            ->method('getOne')
            ->with(\Doofinder\Feed\Helper\Data::MODULE_NAME)
            ->willReturn($moduleInfo);

        $this->assertSame($moduleInfo['setup_version'], $this->_helper->getModuleVersion());
    }
}
