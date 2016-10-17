<?php

namespace Doofinder\Feed\Test\Unit\Observer;

class ProductAtomicUpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Observer\ProductAtomicUpdate
     */
    private $_observer;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $_objectManager;

    /**
     * @var \Magento\Framework\Event
     */
    private $_event;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $_invokedObserver;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $_generator;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $_feedConfig;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_product;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_generator = $this->getMock(
            '\Doofinder\Feed\Model\Generator',
            [],
            [],
            '',
            false
        );

        $this->_generatorFactory = $this->getMock(
            '\Doofinder\Feed\Model\GeneratorFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_generatorFactory->method('create')->willReturn($this->_generator);

        $this->_storeConfig = $this->getMock(
            '\Doofinder\Feed\Helper\StoreConfig',
            [],
            [],
            '',
            false
        );
        $this->_storeConfig->method('getStoreCodes')->willReturn(['default']);
        $this->_storeConfig->method('getHashId')->willReturn('sample_hash_id');
        $this->_storeConfig->method('getApiKey')->willReturn('sample_api_key');

        $this->_feedConfig = $this->getMock(
            '\Doofinder\Feed\Helper\FeedConfig',
            [],
            [],
            '',
            false
        );
        $this->_feedConfig->method('getLeanFeedConfig')->willReturn([
            'data' => [
                'config' => [
                    'sample' => 'value',
                ],
            ],
        ]);

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

        $this->_event = $this->getMock(
            '\Magento\Framework\Event',
            ['getProduct'],
            [],
            '',
            false
        );
        $this->_event->method('getProduct')->willReturn($this->_product);

        $this->_invokedObserver = $this->getMock(
            '\Magento\Framework\Event\Observer',
            [],
            [],
            '',
            false
        );
        $this->_invokedObserver->method('getEvent')->willReturn($this->_event);

        $this->_observer = $this->_objectManager->getObject(
            '\Doofinder\Feed\Observer\ProductAtomicUpdate',
            [
                'generatorFactory' => $this->_generatorFactory,
                'storeConfig' => $this->_storeConfig,
                'feedConfig' => $this->_feedConfig,
            ]
        );
    }

    /**
     * Test execute() method
     */
    public function testExecuteEnabled()
    {
        $feedConfig = [
            'data' => [
                'config' => [
                    'sample' => 'value',
                    'fetchers' => [
                        'Product\Fixed' => [
                            'products' => [$this->_product],
                        ],
                    ],
                    'processors' => [
                        'AtomicUpdater' => [
                            'hash_id' => 'sample_hash_id',
                            'api_key' => 'sample_api_key',
                        ],
                    ],
                ],
            ],
        ];

        $this->_generatorFactory->expects($this->once())->method('create')
            ->with($feedConfig)->willReturn($this->_generator);

        $this->_generator->expects($this->once())->method('run');

        $this->_storeConfig->method('isAtomicUpdatesEnabled')->willReturn(true);
        $this->_observer->execute($this->_invokedObserver);
    }

    /**
     * Test execute() method
     */
    public function testExecuteDisabled()
    {
        $this->_generator->expects($this->never())->method('run');

        $this->_storeConfig->method('isAtomicUpdatesEnabled')->willReturn(false);
        $this->_observer->execute($this->_invokedObserver);
    }
}
