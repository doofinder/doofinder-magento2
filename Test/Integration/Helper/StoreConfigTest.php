<?php

namespace Doofinder\Feed\Test\Integration\Helper;

use Magento\TestFramework\TestCase\AbstractIntegrity;

/**
 * Test class for \Doofinder\Feed\Helper\StoreConfig
 */
class StoreConfigTest extends AbstractIntegrity
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->helper = $this->objectManager->create(
            \Doofinder\Feed\Helper\StoreConfig::class
        );
    }

    /**
     * Test for getStoreCodes() method
     *
     * @param  string $store
     * @param  array $expected
     * @return void
     * @dataProvider providerGetStoreCodes
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoAppIsolation enabled
     */
    public function testGetStores($store, array $expected)
    {
        $storeManager = $this->objectManager->get(
            \Magento\Store\Model\StoreManagerInterface::class
        );

        $storeManager->setCurrentStore($store);
        $stores = $this->helper->getStoreCodes();

        $this->assertEquals($expected, $stores);
    }

    /**
     * Data provider for getStoreCodes() test
     *
     * @return array
     */
    public function providerGetStoreCodes()
    {
        return [
            ['default', ['default', 'test']],
            ['test', ['test']],
        ];
    }
}
