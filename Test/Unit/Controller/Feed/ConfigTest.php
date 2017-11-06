<?php

namespace Doofinder\Feed\Test\Unit\Controller\Feed;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class ConfigTest
 * @package Doofinder\Feed\Test\Unit\Controller\Feed
 */
class ConfigTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Controller\Feed\Config
     */
    private $_controller;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    private $_jsonResult;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $_resultFactory;

    /**
     * @var Magento\Framework\App\ResponseInterface
     */
    private $_response;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $_context;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $_store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $_productMetadata;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $_helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * Prepares the environment before running a test.
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

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

        $this->_resultFactory = $this->getMock(
            '\Magento\Framework\Controller\ResultFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_resultFactory->method('create')
            ->with('json')->willReturn($this->_jsonResult);

        $this->_response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            [],
            [],
            '',
            false
        );

        $this->_context = $this->getMock(
            'Magento\Framework\App\Action\Context',
            [],
            [],
            '',
            false
        );
        $this->_context->method('getResponse')->willReturn($this->_response);
        $this->_context->method('getResultFactory')->willReturn($this->_resultFactory);

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

        $this->_scopeConfig = $scopeConfig = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );
        $this->_scopeConfig->method('getValue')->will($this->returnValueMap([
            ['general/locale/code', $scopeConfig::SCOPE_TYPE_DEFAULT, null, 'EN'],
        ]));

        $this->_schedule = $this->getMock(
            '\Doofinder\Feed\Helper\Schedule',
            [],
            [],
            '',
            false
        );
        $this->_schedule->method('isFeedFileExist')->willReturn(false);
        $this->_schedule->method('getFeedFileUrl')->with('default', false)
            ->willReturn('http://example.com/pub/media//doofinder-default.xml');

        $this->_controller = $this->objectManager->getObject(
            '\Doofinder\Feed\Controller\Feed\Config',
            [
                'storeConfig' => $this->_storeConfig,
                'resultFactory' => $this->_resultFactory,
                'storeManager' => $this->_storeManager,
                'productMetadata' => $this->_productMetadata,
                'helper' => $this->_helper,
                'scopeConfig' => $this->_scopeConfig,
                'schedule' => $this->_schedule,
                'context' => $this->_context,
            ]
        );
    }

    /**
     * Test execute() method.
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

        $this->_jsonResult->expects($this->once())->method('setData')->with($config);
        $this->_controller->execute();
    }
}
