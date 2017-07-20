<?php

namespace Doofinder\Feed\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Helper\Schedule
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScheduleTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_helper;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $_process;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    private $_directory;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $_driver;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $_generator;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\FetcherInterface
     */
    private $_fetcher;

    /**
     * @var \DateTime
     */
    private $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $_timezone;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $_store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    private $_feedLogger;

    /**
     * @var \Doofinder\Feed\Logger\FeedFactory
     */
    private $_feedLoggerFactory;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

        $this->_process = $this->getMock(
            '\Doofinder\Feed\Model\Cron',
            ['getStoreCode', 'save', 'setOffset', 'setComplete', 'setLastFeedName', 'setMessage', 'setErrorStack'],
            [],
            '',
            false
        );
        $this->_process->method('getStoreCode')->willReturn('feed');

        $this->_driver = $this->getMock(
            '\Magento\Framework\Filesystem\DriverInterface',
            [],
            [],
            '',
            false
        );

        $this->_directory = $this->getMock(
            '\Magento\Framework\Filesystem\Directory\Write',
            [],
            [],
            '',
            false
        );
        $this->_directory->method('getAbsolutePath')->will($this->onConsecutiveCalls(
            '/tmp/doofinder-feed.xml.tmp',
            '/tmp/doofinder-feed.xml.tmp',
            '/media/doofinder-feed.xml'
        ));
        $this->_directory->method('getDriver')->willReturn($this->_driver);

        $this->_filesystem = $this->getMock(
            '\Magento\Framework\Filesystem',
            [],
            [],
            '',
            false
        );
        $this->_filesystem->method('getDirectoryRead')->willReturn($this->_directory);
        $this->_filesystem->method('getDirectoryWrite')->willReturn($this->_directory);

        $this->_fetcher = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\FetcherInterface',
            [],
            [],
            '',
            false
        );
        $this->_fetcher->method('getLastProcessedEntityId')->will($this->onConsecutiveCalls(9, 21));

        $this->_generator = $this->getMock(
            '\Doofinder\Feed\Model\Generator',
            [],
            [],
            '',
            false
        );
        $this->_generator->method('getFetcher')->willReturn($this->_fetcher);

        $this->_generatorFactory = $this->getMock(
            '\Doofinder\Feed\Model\GeneratorFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_generatorFactory->method('create')->willReturn($this->_generator);

        $this->_date = $this->getMock(
            '\DateTime',
            [],
            [],
            '',
            false
        );

        $this->_timezone = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            [],
            [],
            '',
            false
        );
        $this->_timezone->method('getConfigTimezone')->willReturn('America/Los_Angeles');
        $this->_timezone->method('date')->willReturn($this->_date);

        $this->_store = $this->getMock(
            'Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $this->_store->method('getBaseUrl')->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->willReturn('http://example.com/media/');

        $this->_storeManager = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->_storeManager->method('getStore')->with('default')->willReturn($this->_store);

        $this->_feedLogger = $this->getMock(
            '\Doofinder\Feed\Logger\Feed',
            [],
            [],
            '',
            false
        );

        $this->_feedLoggerFactory = $this->getMock(
            '\Doofinder\Feed\Logger\FeedFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_feedLoggerFactory->method('create')->willReturn($this->_feedLogger);

        $this->_storeConfig = $this->getMock(
            '\Doofinder\Feed\Helper\StoreConfig',
            [],
            [],
            '',
            false
        );

        $this->_helper = $this->objectManager->getObject(
            '\Doofinder\Feed\Helper\Schedule',
            [
                'filesystem' => $this->_filesystem,
                'generatorFactory' => $this->_generatorFactory,
                'timezone' => $this->_timezone,
                'storeManager' => $this->_storeManager,
                'feedLoggerFactory' => $this->_feedLoggerFactory,
                'storeConfig' => $this->_storeConfig,
            ]
        );
    }

    /**
     * Test runProcess() method
     */
    public function testRunProcess()
    {
        $this->_fetcher->method('getProgress')->willReturn(0.49);

        $this->_fetcher->method('isDone')->willReturn(false);

        $this->_driver->expects($this->never())->method('rename');

        $this->_process->expects($this->once())->method('setOffset')
            ->with(9);

        $this->_process->expects($this->once())->method('setComplete')
            ->with('49.0%');

        $this->_process->expects($this->never())->method('setLastFeedName');

        $this->_process->expects($this->never())->method('setMessage');

        $this->_process->expects($this->once())->method('save');

        $this->_helper->runProcess($this->_process);
    }

    /**
     * Test runProcess() method
     */
    public function testRunProcessDone()
    {
        $this->_fetcher->method('getProgress')->willReturn(1.0);

        $this->_fetcher->method('isDone')->willReturn(true);

        $this->_driver->expects($this->once())->method('rename')
            ->with('/tmp/doofinder-feed.xml.tmp', '/media/doofinder-feed.xml', $this->_driver)
            ->willReturn(true);

        $this->_process->expects($this->once())->method('setOffset')
            ->with(9);

        $this->_process->expects($this->once())->method('setComplete')
            ->with('100.0%');

        $this->_process->expects($this->once())->method('setLastFeedName')
            ->with('doofinder-feed.xml');

        $this->_process->expects($this->once())->method('setMessage')
            ->with('Last process successfully completed. Now waiting for new schedule.');

        $this->_process->expects($this->once())->method('save');

        $this->_helper->runProcess($this->_process);
    }

    /**
     * Test runProcess() method
     */
    public function testRunProcessRenameError()
    {
        $this->_fetcher->method('isDone')->willReturn(true);

        $this->_driver->expects($this->once())->method('rename')
            ->with('/tmp/doofinder-feed.xml.tmp', '/media/doofinder-feed.xml', $this->_driver)
            ->willReturn(false);

        $this->_process->expects($this->never())->method('setLastFeedName');

        $this->_process->expects($this->once())->method('setMessage')
            ->with('#error#Cannot rename doofinder-feed.xml.tmp to doofinder-feed.xml');

        $this->_process->expects($this->once())->method('setErrorStack')->with(1);

        $this->_process->expects($this->once())->method('save');

        $this->_helper->runProcess($this->_process);
    }

    /**
     * Test isFeedFileExist() method
     *
     * @dataProvider testIsFeedFileExistProvider
     */
    public function testIsFeedFileExist($expected)
    {
        $this->_directory->expects($this->once())->method('isExist')
            ->with('doofinder-default.xml')->willReturn($expected);

        $this->assertEquals(
            $expected,
            $this->_helper->isFeedFileExist('default')
        );
    }

    public function testIsFeedFileExistProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * Test getFeedFileUrl() method
     *
     * @dataProvider testGetFeedFileUrlProvider
     */
    public function testGetFeedFileUrl($storeCode, $passwordConfig, $password, $expected)
    {
        $this->_storeConfig->method('getStoreConfig')->with($storeCode)->willReturn(['password' => $passwordConfig]);

        $this->assertEquals(
            $expected,
            $this->_helper->getFeedFileUrl($storeCode, $password)
        );
    }

    public function testGetFeedFileUrlProvider()
    {
        return [
            ['default', null, true, 'http://example.com/media/doofinder-default.xml'],
            ['default', 'secret', true, 'http://example.com/media/doofinder-default-secret.xml'],
            ['default', 'secret', false, 'http://example.com/media/doofinder-default.xml'],
            ['default', null, 'secret', 'http://example.com/media/doofinder-default-secret.xml'],
            ['default', 'secret', 'other', 'http://example.com/media/doofinder-default-other.xml'],
        ];
    }
}
