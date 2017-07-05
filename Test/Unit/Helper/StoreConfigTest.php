<?php

namespace Doofinder\Feed\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class StoreConfigTest
 * @package Doofinder\Feed\Test\Unit\Helper
 */
class StoreConfigTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $_storeInterface;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_helper;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_scopeConfig = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $this->_storeManager = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->_storeInterface = $this->getMock(
            '\Magento\Store\Api\Data\StoreInterface',
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
            '\Doofinder\Feed\Helper\StoreConfig',
            [
                'scopeConfig'  => $this->_scopeConfig,
                'storeManager'    => $this->_storeManager,
                'logger'        => $this->_logger,
            ]
        );
    }

    /**
     * Test getStoreConfig() method.
     */
    public function testGetStoreConfig()
    {
        $this->_storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeInterface);

        $this->_storeInterface->expects($this->once())
            ->method('getCode')
            ->willReturn('default');

        $this->_scopeConfig->expects($this->at(0))
            ->method('getValue')
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_ATTRIBUTES_CONFIG)
            ->willReturn(['attr1' => 'value1', 'attr2' => 'value2']);

        $this->_scopeConfig->expects($this->at(1))
            ->method('getValue')
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_CRON_CONFIG)
            ->willReturn(['enabled' => 1, 'start_time' => '10,30,0']);

        $this->_scopeConfig->expects($this->at(2))
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
        $this->_storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeInterface);

        $this->_storeInterface->expects($this->once())
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
        $this->_storeManager->method('getStore')->willReturn($store);
        $this->_storeManager->method('getStores')->willReturn($stores);

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

        $this->_scopeConfig
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

        $this->_scopeConfig
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

        $this->_scopeConfig->method('getValue')->with('catalog/search/engine', 'store', $storeCode)
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

        $this->_scopeConfig->method('getValue')->will($this->returnValueMap([
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

    /**
     * Test isExportCategoriesInNavigation() method.
     *
     * @dataProvider testIsExportCategoriesInNavigationProvider
     */
    public function testIsExportCategoriesInNavigation($value, $expected)
    {
        $storeCode = 'sample';

        $this->_scopeConfig->method('getValue')->will($this->returnValueMap([
            ['doofinder_feed_feed/feed_settings/categories_in_navigation', 'store', $storeCode, $value],
        ]));

        $this->assertEquals($expected, $this->_helper->isExportCategoriesInNavigation($storeCode));
    }

    public function testIsExportCategoriesInNavigationProvider()
    {
        return [
            [true,  true],
            [false, false],
        ];
    }

    /**
     * Test getSearchLayerScript() method.
     */
    public function testGetSearchLayerScript()
    {
        $storeCode = 'sample';
        $script = '<script type="text/javascript">sample script</script>';

        $this->_scopeConfig->method('getValue')->will($this->returnValueMap([
            ['doofinder_feed_search/doofinder_layer/script', 'store', $storeCode, $script],
        ]));

        $this->assertEquals($script, $this->_helper->getSearchLayerScript($storeCode));
    }
}
