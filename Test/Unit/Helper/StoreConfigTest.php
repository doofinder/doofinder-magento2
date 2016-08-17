<?php

namespace Doofinder\Feed\Test\Unit\Helper;

/**
 * Class StoreConfigTest
 * @package Doofinder\Feed\Test\Unit\Helper
 */
class StoreConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManage
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagerMock;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_loggerMock;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $_storeInterfaceMock;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_helper;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_scopeConfigMock = $this->getMock(
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

        $this->_loggerMock = $this->getMock(
            '\Psr\Log\LoggerInterface',
            [],
            [],
            '',
            false
        );

        $this->_helper = $this->_objectManager->getObject(
            '\Doofinder\Feed\Helper\StoreConfig',
            [
                'scopeConfig'  => $this->_scopeConfigMock,
                'storeManager'    => $this->_storeManagerMock,
                'logger'        => $this->_loggerMock,
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

        $this->_scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_CRON_CONFIG)
            ->willReturn(['enabled' => 1, 'start_time' => '10,30,0']);

        $this->_scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_SETTINGS_CONFIG)
            ->willReturn(['grouped' => 0]);

        $expected = array(
            'store_code'    => 'default',
            'enabled'       => 1,
            'grouped'       =>  0,
            'start_time'    => ['10', '30', '0'],
        );

        $result = $this->_helper->getStoreConfig();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getStoreCode() method.
     */
    public function testGetStoreCode()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeInterfaceMock);

        $this->_storeInterfaceMock->expects($this->once())
            ->method('getCode')
            ->willReturn('default');

        $expected = 'default';

        $this->assertSame($expected, $this->_helper->getStoreCode());
    }

    /**
     * Test setCurrentStore() method with valid data.
     */
    public function testSetCurrentStoreWithRealStoreId()
    {
        $storeId = 1;

        $mock = $this->getMock(
            '\Magento\Store\Api\Data\StoreInterface',
            [],
            [],
            '',
            false
        );

        $this->_storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($mock);

        $this->_storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with($storeId);

        $this->_helper->setCurrentStore($storeId);
    }

    /**
     * Test setCurrentStore() method with invalid data.
     *
     * @todo throws exception
     */
    public function testSetCurrentStoreWithWrongStoreId()
    {
        $storeId = 30;

        $this->_storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn(false);

        $this->_storeManagerMock->expects($this->never())
            ->method('setCurrentStore')
            ->with($storeId);

        $this->_helper->setCurrentStore($storeId);
    }
}
