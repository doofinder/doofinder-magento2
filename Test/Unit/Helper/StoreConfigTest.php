<?php

namespace Doofinder\Feed\Test\Unit\Helper;

/**
 * Test class for \Doofinder\Feed\Helper\StoreConfig
 */
class StoreConfigTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManager
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
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $storeWebsiteRelation;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $helper;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeInterface = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeWebsiteRelation = $this->getMockBuilder(\Doofinder\Feed\Model\StoreWebsiteRelation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->objectManager->getObject(
            \Doofinder\Feed\Helper\StoreConfig::class,
            [
                'scopeConfig'           => $this->scopeConfig,
                'storeManager'          => $this->storeManager,
                'logger'                => $this->logger,
                'storeWebsiteRelation'  => $this->storeWebsiteRelation,
                '_request'              => $this->request,
            ]
        );
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
     * Test getStoreCodes by store id
     *
     * @return void
     */
    public function testGetStoreCodesByStoreId()
    {
        $storeId = 1;
        $storeCode = 'store_code';
        $this->request->method('getParam')->with('store')->willReturn($storeId);

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->method('getCode')->willReturn($storeCode);

        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);
        $this->assertEquals([$storeCode], $this->helper->getStoreCodes());
    }

    /**
     * Test getStoreCodes by website id
     *
     * @return void
     */
    public function testGetStoreCodesByWebsiteId()
    {
        $expected = ['store_one', 'store_two'];

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $store->method('getCode')
            ->willReturnOnConsecutiveCalls('store_one', 'store_two');

        $store->method('isActive')
            ->willReturnOnConsecutiveCalls(true, true);

        $this->request->expects($this->at(1))->method('getParam')->with('website')->willReturn(1);
        $this->storeWebsiteRelation->method('getStoreByWebsiteId')->with(1)->willReturn([1, 2]);

        $this->storeManager->expects($this->exactly(2))
            ->method('getStore')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($store, $store);

        $this->assertEquals($expected, $this->helper->getStoreCodes());
    }

    /**
     * Test getStoreCodes without specified website or store
     * @param boolean $onlyActive
     * @param array $expected
     * @return void
     * @dataProvider providerTestGetStoreCodes
     */
    public function testGetStoreCodes($onlyActive, array $expected)
    {
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store
            ->method('getCode')
            ->willReturnOnConsecutiveCalls('active', 'inactive');
        $store
            ->method('isActive')
            ->willReturnOnConsecutiveCalls(true, false);
        $stores = [$store, $store];

        $this->request->method('getParam')->willReturn(null);
        $this->storeManager->method('getStores')->willReturn($stores);

        $this->assertEquals($expected, $this->helper->getStoreCodes($onlyActive));
    }

    /**
     * Data provider for testGetStoreCodes()
     *
     * @return array
     */
    public function providerTestGetStoreCodes()
    {
        return [
            [true, ['active']],
            [false, ['active', 'inactive']]
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
        $this->scopeConfig->method('getValue')->with('catalog/search/engine', 'store', null)
            ->willReturn($enabled);

        $this->assertEquals($expected, $this->helper->isInternalSearchEnabled());
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
