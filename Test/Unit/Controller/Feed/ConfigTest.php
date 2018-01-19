<?php

namespace Doofinder\Feed\Test\Unit\Controller\Feed;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Controller\Feed\Config
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Controller\Feed\Config
     */
    private $controller;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    private $jsonResult;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * Set up test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

        $this->storeConfig = $this->getMock(
            \Doofinder\Feed\Helper\StoreConfig::class,
            [],
            [],
            '',
            false
        );

        $this->jsonResult = $this->getMock(
            \Magento\Framework\Controller\Result\Json::class,
            [],
            [],
            '',
            false
        );

        $this->resultFactory = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultFactory->method('create')
            ->with('json')->willReturn($this->jsonResult);

        $this->response = $this->getMock(
            \Magento\Framework\App\ResponseInterface::class,
            [],
            [],
            '',
            false
        );

        $this->context = $this->getMock(
            \Magento\Framework\App\Action\Context::class,
            [],
            [],
            '',
            false
        );
        $this->context->method('getResponse')->willReturn($this->response);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);

        $this->store = $this->getMock(
            \Magento\Store\Model\Store::class,
            [],
            [],
            '',
            false
        );
        $this->store->method('getCode')->willReturn('default');
        $this->store->method('getUrl')->with('doofinder/feed')->willReturn(
            'http://example.com/index.php/doofinder/feed/'
        );
        $this->store->method('getCurrentCurrencyCode')->willReturn('USD');

        $this->storeManager = $this->getMock(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            [],
            '',
            false
        );
        $this->storeManager->method('getStore')->willReturn($this->store);
        $this->storeManager->method('getStores')->willReturn([$this->store]);

        $this->productMetadata = $this->getMock(
            \Magento\Framework\App\ProductMetadataInterface::class,
            [],
            [],
            '',
            false
        );
        $this->productMetadata->method('getVersion')->willReturn('x.y.z');
        $this->productMetadata->method('getEdition')->willReturn('Community');

        $this->helper = $this->getMock(
            \Doofinder\Feed\Helper\Data::class,
            [],
            [],
            '',
            false
        );
        $this->helper->method('getModuleVersion')->willReturn('k.l.m');

        $this->scopeConfig = $scopeConfig = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            [],
            '',
            false
        );
        $this->scopeConfig->method('getValue')->will($this->returnValueMap([
            ['general/locale/code', $scopeConfig::SCOPE_TYPE_DEFAULT, null, 'EN'],
        ]));

        $this->schedule = $this->getMock(
            \Doofinder\Feed\Helper\Schedule::class,
            [],
            [],
            '',
            false
        );
        $this->schedule->method('isFeedFileExist')->willReturn(false);
        $this->schedule->method('getFeedFileUrl')->with('default', false)
            ->willReturn('http://example.com/pub/media//doofinder-default.xml');

        $this->controller = $this->objectManager->getObject(
            \Doofinder\Feed\Controller\Feed\Config::class,
            [
                'storeConfig' => $this->storeConfig,
                'resultFactory' => $this->resultFactory,
                'storeManager' => $this->storeManager,
                'productMetadata' => $this->productMetadata,
                'helper' => $this->helper,
                'scopeConfig' => $this->scopeConfig,
                'schedule' => $this->schedule,
                'context' => $this->context,
            ]
        );
    }

    /**
     * Test execute() method
     *
     * @return void
     */
    public function testExecuteEnabled()
    {
        $this->storeConfig->method('getStoreConfig')->willReturn([
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

        $this->jsonResult->expects($this->once())->method('setData')->with($config);
        $this->controller->execute();
    }
}
