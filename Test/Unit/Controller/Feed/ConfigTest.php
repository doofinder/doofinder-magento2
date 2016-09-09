<?php

namespace Doofinder\Feed\Test\Unit\Controller\Feed;

/**
 * Class ConfigTest
 * @package Doofinder\Feed\Test\Unit\Controller\Feed
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Doofinder\Feed\Controller\Feed\Config
     */
    protected $_controller;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     *
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $_jsonResult;

    /**
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_storeConfig = $this->getMock(
            '\Doofinder\Feed\Helper\StoreConfig',
            [],
            [],
            '',
            false
        );

        $this->_jsonResult = $this->getMock(
            '\Magento\Framework\Controller\Result\Json',
            [],
            [],
            '',
            false
        );

        $this->_jsonResultFactory = $this->getMock(
            '\Magento\Framework\Controller\Result\JsonFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_jsonResultFactory->method('create')->willReturn($this->_jsonResult);

        $this->_store = $this->getMock(
            '\Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $this->_store->method('getCode')->willReturn('default');
        $this->_store->method('getUrl')->with('doofinder/feed')->willReturn(
            'http://example.com/index.php/doofinder/feed/'
        );
        $this->_store->method('getCurrentCurrencyCode')->willReturn('USD');

        $this->_storeManager = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->_storeManager->method('getStore')->willReturn($this->_store);
        $this->_storeManager->method('getStores')->willReturn([$this->_store]);

        $this->_productMetadata = $this->getMock(
            '\Magento\Framework\App\ProductMetadataInterface',
            [],
            [],
            '',
            false
        );
        $this->_productMetadata->method('getVersion')->willReturn('x.y.z');
        $this->_productMetadata->method('getEdition')->willReturn('Community');

        $this->_helper = $this->getMock(
            '\Doofinder\Feed\Helper\Data',
            [],
            [],
            '',
            false
        );
        $this->_helper->method('getModuleVersion')->willReturn('k.l.m');

        $this->_scopeConfig = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );
        $this->_scopeConfig->method('getValue')->will($this->returnValueMap([
            ['general/locale/code', $this->_scopeConfig::SCOPE_TYPE_DEFAULT, null, 'EN'],
        ]));

        $this->_schedule = $this->getMock(
            '\Doofinder\Feed\Helper\Schedule',
            [],
            [],
            '',
            false
        );
        $this->_schedule->method('isFeedFileExist')->willReturn(false);
        $this->_schedule->method('getFeedFileUrl')->willReturn('http://example.com/pub/media//doofinder-default.xml');

        $this->_controller = $this->_objectManager->getObject(
            '\Doofinder\Feed\Controller\Feed\Config',
            [
                'storeConfig' => $this->_storeConfig,
                'jsonResultFactory' => $this->_jsonResultFactory,
                'storeManager' => $this->_storeManager,
                'productMetadata' => $this->_productMetadata,
                'helper' => $this->_helper,
                'scopeConfig' => $this->_scopeConfig,
                'schedule' => $this->_schedule,
            ]
        );
    }

    /**
     * Test execute() method.
     *
     * @todo wait for feed/config controller implementation
     */
    public function testExecuteEnabled()
    {
        $this->_storeConfig->method('getStoreConfig')->willReturn([
            'enabled' => true,
        ]);

        $config = [
            'platform' => [
                'name' => 'Magento',
                'edition' => 'Community',
                'version' => 'x.y.z',
            ],
            'module' => [
                'version' => 'k.l.m',
                'feed' => 'http://example.com/index.php/doofinder/feed/',
                'options' => [
                    'language' => [
                        'default',
                    ],
                ],
                'configuration' => [
                    'default' => [
                        'language' => 'EN',
                        'currency' => 'USD',
                        'feed_url' => 'http://example.com/pub/media//doofinder-default.xml',
                        'feed_exists' => false,
                    ],
                ],
            ],
        ];

        $this->_jsonResult->expects($this->at(1))->method('setData')->with($config);
        $this->_controller->execute();
    }
}
