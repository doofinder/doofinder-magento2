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
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_ATTRIBUTES_CONFIG)
            ->willReturn(['attr1' => 'value1', 'attr2' => 'value2']);

        $this->_scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_CRON_CONFIG)
            ->willReturn(['enabled' => 1, 'start_time' => '10,30,0']);

        $this->_scopeConfigMock->expects($this->at(2))
            ->method('getValue')
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_SETTINGS_CONFIG)
            ->willReturn(['split_configurable_products' => 0, 'image_size' => 'small', 'export_product_prices' => 1]);

        $expected = [
            'store_code'                    => 'default',
            'enabled'                       => 1,
            'split_configurable_products'   => 0,
            'export_product_prices'         => 1,
            'image_size'                    => 'small',
            'start_time'                    => ['10', '30', '0'],
            'attributes'                    => ['attr1' => 'value1', 'attr2' => 'value2'],
        ];

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
     * Test getStoreCodes() method.
     *
     * @dataProvider testGetStoreCodesProvider
     */
    public function testGetStoreCodes($onlyActive, $current, $stores, $expected)
    {
        $store = $this->getMock(
            '\Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $store->method('getCode')->willReturn($current);
        $this->_storeManagerMock->method('getStore')->willReturn($store);
        $this->_storeManagerMock->method('getStores')->willReturn($stores);

        $this->assertEquals($expected, $this->_helper->getStoreCodes($onlyActive));
    }

    public function testGetStoreCodesProvider()
    {
        $stores = [];

        $store = $this->getMock(
            '\Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $store->method('isActive')->willReturn(true);
        $store->method('getCode')->willReturn('active');
        $stores[] = $store;

        $store = $this->getMock(
            '\Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $store->method('isActive')->willReturn(false);
        $store->method('getCode')->willReturn('inactive');
        $stores[] = $store;

        return [
            [true, 'admin', $stores, ['active']],
            [false, 'admin', $stores, ['active', 'inactive']],
            [true, 'sample', $stores, ['sample']],
        ];
    }

    /**
     * Test getApiKey() method.
     */
    public function testGetApiKey()
    {
        $expected = 'sample_api_key';

        $this->_scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('doofinder_feed_search/doofinder_internal_search/api_key', 'default', null)
            ->willReturn($expected);

        $this->assertSame($expected, $this->_helper->getApiKey());
    }

    /**
     * Test getHashId() method.
     */
    public function testGetHashId()
    {
        $storeCode = 'sample';
        $expected = 'sample_hash_id';

        $this->_scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('doofinder_feed_search/doofinder_internal_search/hash_id', 'store', $storeCode)
            ->willReturn($expected);

        $this->assertSame($expected, $this->_helper->getHashId($storeCode));
    }

    /**
     * Test isInternalSearchEnabled() method.
     *
     * @dataProvider testIsInternalSearchEnabledProvider
     */
    public function testIsInternalSearchEnabled($enabled, $expected)
    {
        $storeCode = 'sample';

        $this->_scopeConfigMock->method('getValue')->with('catalog/search/engine', 'store', $storeCode)
            ->willReturn($enabled);

        $this->assertEquals($expected, $this->_helper->isInternalSearchEnabled($storeCode));
    }

    public function testIsInternalSearchEnabledProvider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * Test isAtomicUpdatesEnabled() method.
     *
     * @dataProvider testIsAtomicUpdatesEnabledProvider
     */
    public function testIsAtomicUpdatesEnabled($engine, $atomic, $expected)
    {
        $storeCode = 'sample';

        $this->_scopeConfigMock->method('getValue')->will($this->returnValueMap([
            ['catalog/search/engine', 'store', $storeCode, $engine],
            ['doofinder_feed_feed/feed_settings/atomic_updates_enabled', 'store', $storeCode, $atomic],
        ]));

        $this->assertEquals($expected, $this->_helper->isAtomicUpdatesEnabled($storeCode));
    }

    public function testIsAtomicUpdatesEnabledProvider()
    {
        return [
            [true, true, true],
            [false, false, false],
            [true, false, false],
            [false, true, false],
        ];
    }
}
