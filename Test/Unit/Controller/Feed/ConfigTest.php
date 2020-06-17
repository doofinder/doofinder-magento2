<?php

namespace Doofinder\Feed\Test\Unit\Controller\Feed;

/**
 * Test class for \Doofinder\Feed\Controller\Feed\Config
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
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
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Doofinder\Feed\Helper\ComposerVersionProvider
     */
    private $composerVersion;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Set up test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setupTests()
    {
        $this->storeConfig = $this->getMockBuilder(\Doofinder\Feed\Helper\StoreConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonResult = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->method('create')
            ->with('json')->willReturn($this->jsonResult);

        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->method('getResponse')->willReturn($this->response);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store->method('getCode')->willReturn('default');
        $this->store->method('getId')->willReturn(1);
        $this->store->method('getCurrentCurrencyCode')->willReturn('USD');

        $this->productMetadata = $this->getMockBuilder(\Magento\Framework\App\ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMetadata->method('getVersion')->willReturn('x.y.z');
        $this->productMetadata->method('getEdition')->willReturn('Community');

        $this->scopeConfig = $scopeConfig = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->storeConfig->expects($this->once())->method('getAllStores')->willReturn([$this->store]);
        $this->storeConfig->expects($this->once())->method('getStoreLanguage')->willReturn('EN');

        $this->composerVersion = $this->getMockBuilder(
            \Doofinder\Feed\Helper\ComposerVersionProvider::class
        )->disableOriginalConstructor()->getMock();
        $this->composerVersion->expects($this->once())
            ->method('getComposerVersion')
            ->willReturn('k.l.m');

        $this->scopeConfig->method('getValue')->will($this->returnValueMap([
            ['general/locale/code', $scopeConfig::SCOPE_TYPE_DEFAULT, null, 'EN'],
        ]));

        $this->controller = $this->objectManager->getObject(
            \Doofinder\Feed\Controller\Feed\Config::class,
            [
                'storeConfig' => $this->storeConfig,
                'productMetadata' => $this->productMetadata,
                'composerVersion' => $this->composerVersion,
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
        $config = [
            'platform' => [
                'name' => 'Magento',
                'edition' => 'Community',
                'version' => 'x.y.z',
            ],
            'module' => [
                'version' => 'k.l.m',
                'options' => [
                    'language' => [
                        'default',
                    ],
                ],
                'configuration' => [
                    'default' => [
                        'language' => 'EN',
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $this->jsonResult->expects($this->once())->method('setData')->with($config);
        $this->controller->execute();
    }
}
