<?php

namespace Doofinder\Feed\Test\Integration\Helper;

/**
 * Test class for \Doofinder\Feed\Helper\StoreConfig
 */
class StoreConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_helper = $this->_objectManager->create(
            '\Doofinder\Feed\Helper\StoreConfig'
        );
    }

    /**
     * Test for getStoreCodes() method.
     *
     * @dataProvider testGetStoreCodesProvider
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoAppIsolation enabled
     */
    public function testGetStores($store, $expected)
    {
        $storeManager = $this->_objectManager->get(
            '\Magento\Store\Model\StoreManagerInterface'
        );

        $storeManager->setCurrentStore($store);
        $stores = $this->_helper->getStoreCodes();
        //$storeManager->setCurrentStore('default');

        $this->assertEquals($expected, $stores);
    }

    public function testGetStoreCodesProvider()
    {
        return [
            ['default', ['default']],
            ['test', ['test']],
        ];
    }
}
