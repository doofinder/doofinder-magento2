<?php

namespace Doofinder\Feed\Test\Unit\Helper;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Helper\StoreConfig
 */
class StoreConfigTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $storeInterface;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $helper;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->scopeConfig = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            [],
            '',
            false
        );

        $this->storeManager = $this->getMock(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            [],
            '',
            false
        );

        $this->storeInterface = $this->getMock(
            \Magento\Store\Api\Data\StoreInterface::class,
            [],
            [],
            '',
            false
        );

        $this->logger = $this->getMock(
            \Psr\Log\LoggerInterface::class,
            [],
            [],
            '',
            false
        );

        $this->helper = $this->objectManager->getObject(
            \Doofinder\Feed\Helper\StoreConfig::class,
            [
                'scopeConfig'  => $this->scopeConfig,
                'storeManager'    => $this->storeManager,
                'logger'        => $this->logger,
            ]
        );
    }

    /**
     * Test getStoreConfig() method
     *
     * @return void
     */
    public function testGetStoreConfig()
    {
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterface);

        $this->storeInterface->expects($this->once())
            ->method('getCode')
            ->willReturn('default');

        $this->scopeConfig->expects($this->at(0))
            ->method('getValue')
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_ATTRIBUTES_CONFIG)
            ->willReturn(['attr1' => 'value1', 'attr2' => 'value2']);

        $this->scopeConfig->expects($this->at(1))
            ->method('getValue')
            ->with(\Doofinder\Feed\Helper\StoreConfig::FEED_CRON_CONFIG)
            ->willReturn(['enabled' => 1, 'start_time' => '10,30,0']);

        $this->scopeConfig->expects($this->at(2))
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

        $result = $this->helper->getStoreConfig();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getStoreCode() method
     *
     * @return void
     */
    public function testGetStoreCode()
    {
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterface);

        $this->storeInterface->expects($this->once())
            ->method('getCode')
            ->willReturn('default');

        $expected = 'default';

        $this->assertSame($expected, $this->helper->getStoreCode());
    }

    /**
     * Test getStoreCodes() method
     *
     * @param  boolean $onlyActive
     * @param  string $current
     * @param  array $stores
     * @param  array $expected
     * @return void
     * @dataProvider providerTestGetStoreCodes
     */
    public function testGetStoreCodes($onlyActive, $current, array $stores, array $expected)
    {
        $store = $this->getMock(
            \Magento\Store\Model\Store::class,
            [],
            [],
            '',
            false
        );
        $store->method('getCode')->willReturn($current);
        $this->storeManager->method('getStore')->willReturn($store);
        $this->storeManager->method('getStores')->willReturn($stores);

        $this->assertEquals($expected, $this->helper->getStoreCodes($onlyActive));
    }

    /**
     * Data provider for testGetStoresCodes() test
     *
     * @return array
     */
    public function providerTestGetStoreCodes()
    {
        $stores = [];

        $store = $this->getMock(
            \Magento\Store\Model\Store::class,
            [],
            [],
            '',
            false
        );
        $store->method('isActive')->willReturn(true);
        $store->method('getCode')->willReturn('active');
        $stores[] = $store;

        $store = $this->getMock(
            \Magento\Store\Model\Store::class,
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
     * Test getApiKey() method
     *
     * @return void
     */
    public function testGetApiKey()
    {
        $expected = 'sample_api_key';

        $this->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with('doofinder_config_config/doofinder_account/api_key', 'default', null)
            ->willReturn($expected);

        $this->assertSame($expected, $this->helper->getApiKey());
    }

    /**
     * Test getHashId() method
     *
     * @return void
     */
    public function testGetHashId()
    {
        $storeCode = 'sample';
        $expected = 'sample_hash_id';

        $this->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with('doofinder_config_config/doofinder_search_engine/hash_id', 'store', $storeCode)
            ->willReturn($expected);

        $this->assertSame($expected, $this->helper->getHashId($storeCode));
    }

    /**
     * Test isInternalSearchEnabled() method.
     *
     * @param  boolean $enabled
     * @param  boolean $expected
     * @return void
     * @dataProvider providerTestIsInternalSearchEnabled
     */
    public function testIsInternalSearchEnabled($enabled, $expected)
    {
        $storeCode = 'sample';

        $this->scopeConfig->method('getValue')->with('catalog/search/engine', 'store', $storeCode)
            ->willReturn($enabled);

        $this->assertEquals($expected, $this->helper->isInternalSearchEnabled($storeCode));
    }

    /**
     * Data provider for testIsInternalSearchEnabled() test
     *
     * @return array
     */
    public function providerTestIsInternalSearchEnabled()
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * Test isAtomicUpdatesEnabled() method.
     *
     * @param  string $engine
     * @param  boolean $atomic
     * @param  boolean $expected
     * @return void
     * @dataProvider providerTestIsAtomicUpdatesEnabled
     */
    public function testIsAtomicUpdatesEnabled($engine, $atomic, $expected)
    {
        $storeCode = 'sample';

        $this->scopeConfig->method('getValue')->will($this->returnValueMap([
            ['catalog/search/engine', 'store', $storeCode, $engine],
            ['doofinder_config_index/feed_settings/atomic_updates_enabled', 'store', $storeCode, $atomic],
        ]));

        $this->assertEquals($expected, $this->helper->isAtomicUpdatesEnabled($storeCode));
    }

    /**
     * Data provider for testIsAtomicUpdatesEnabled() test
     *
     * @return array
     */
    public function providerTestIsAtomicUpdatesEnabled()
    {
        return [
            [true, true, false],
            [false, false, false],
            [true, false, false],
            [false, true, true],
        ];
    }

    /**
     * Test isExportCategoriesInNavigation() method.
     *
     * @param  boolean $value
     * @param  boolean $expected
     * @return void
     * @dataProvider providerTestIsExportCategoriesInNavigation
     */
    public function testIsExportCategoriesInNavigation($value, $expected)
    {
        $storeCode = 'sample';

        $this->scopeConfig->method('getValue')->will($this->returnValueMap([
            ['doofinder_config_index/feed_settings/categories_in_navigation', 'store', $storeCode, $value],
        ]));

        $this->assertEquals($expected, $this->helper->isExportCategoriesInNavigation($storeCode));
    }

    /**
     * Data provider for testIsExportCategoriesInNavigation() test
     *
     * @return array
     */
    public function providerTestIsExportCategoriesInNavigation()
    {
        return [
            [true,  true],
            [false, false],
        ];
    }

    /**
     * Test getSearchLayerScript() method
     *
     * @return void
     */
    public function testGetSearchLayerScript()
    {
        $storeCode = 'sample';
        $script = '<script type="text/javascript">sample script</script>';

        $this->scopeConfig->method('getValue')->will($this->returnValueMap([
            ['doofinder_config_config/doofinder_layer/script', 'store', $storeCode, $script],
        ]));

        $this->assertEquals($script, $this->helper->getSearchLayerScript($storeCode));
    }
}
