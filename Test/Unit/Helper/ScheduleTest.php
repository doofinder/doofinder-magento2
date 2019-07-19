<?php

namespace Doofinder\Feed\Test\Unit\Helper;

/**
 * Test class for \Doofinder\Feed\Helper\Schedule
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScheduleTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $helper;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $process;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    private $directory;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $driver;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $generator;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\FetcherInterface
     */
    private $fetcher;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    private $feedLogger;

    /**
     * @var \Doofinder\Feed\Logger\FeedFactory
     */
    private $feedLoggerFactory;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * Set up test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

        $this->process = $this->getMockBuilder(\Doofinder\Feed\Model\Cron::class)
            ->setMethods([
                'getStoreCode', 'save', 'setOffset', 'setComplete', 'setLastFeedName', 'setMessage', 'setErrorStack'
            ])->disableOriginalConstructor()
            ->getMock();
        $this->process->method('getStoreCode')->willReturn('feed');

        $this->driver = $this->getMockBuilder(\Magento\Framework\Filesystem\DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Write::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directory->method('getAbsolutePath')->will($this->onConsecutiveCalls(
            '/tmp/doofinder-feed.xml.tmp',
            '/tmp/doofinder-feed.xml.tmp',
            '/media/doofinder-feed.xml'
        ));
        $this->directory->method('getDriver')->willReturn($this->driver);

        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->method('getDirectoryRead')->willReturn($this->directory);
        $this->filesystem->method('getDirectoryWrite')->willReturn($this->directory);

        $this->fetcher = $this->getMockBuilder(\Doofinder\Feed\Model\Generator\Component\FetcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcher->method('getLastProcessedEntityId')->will($this->onConsecutiveCalls(9, 21));

        $this->generator = $this->getMockBuilder(\Doofinder\Feed\Model\Generator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->generator->method('getFetcher')->willReturn($this->fetcher);

        $this->generatorFactory = $this->getMockBuilder(\Doofinder\Feed\Model\GeneratorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->generatorFactory->method('create')->willReturn($this->generator);

        $this->date = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->timezone = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezone->method('getConfigTimezone')->willReturn('America/Los_Angeles');
        $this->timezone->method('date')->willReturn($this->date);

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store->method('getBaseUrl')->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->willReturn('http://example.com/media/');

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->method('getStore')->with('default')->willReturn($this->store);

        $this->feedLogger = $this->getMockBuilder(\Doofinder\Feed\Logger\Feed::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->feedLoggerFactory = $this->getMockBuilder(\Doofinder\Feed\Logger\FeedFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedLoggerFactory->method('create')->willReturn($this->feedLogger);

        $this->storeConfig = $this->getMockBuilder(\Doofinder\Feed\Helper\StoreConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->objectManager->getObject(
            \Doofinder\Feed\Helper\Schedule::class,
            [
                'filesystem' => $this->filesystem,
                'generatorFactory' => $this->generatorFactory,
                'timezone' => $this->timezone,
                'storeManager' => $this->storeManager,
                'feedLoggerFactory' => $this->feedLoggerFactory,
                'storeConfig' => $this->storeConfig,
            ]
        );
    }

    /**
     * Test runProcess() method
     *
     * @return void
     */
    public function testRunProcess()
    {
        $this->fetcher->method('getProgress')->willReturn(0.49);

        $this->fetcher->method('isDone')->willReturn(false);

        $this->driver->expects($this->never())->method('rename');

        $this->process->expects($this->once())->method('setOffset')
            ->with(9);

        $this->process->expects($this->once())->method('setComplete')
            ->with('49.0%');

        $this->process->expects($this->never())->method('setLastFeedName');

        $this->process->expects($this->never())->method('setMessage');

        $this->process->expects($this->once())->method('save');

        $this->helper->runProcess($this->process);
    }

    /**
     * Test runProcess() method
     *
     * @return void
     */
    public function testRunProcessDone()
    {
        $this->fetcher->method('getProgress')->willReturn(1.0);

        $this->fetcher->method('isDone')->willReturn(true);

        $this->driver->expects($this->once())->method('rename')
            ->with('/tmp/doofinder-feed.xml.tmp', '/media/doofinder-feed.xml', $this->driver)
            ->willReturn(true);

        $this->process->expects($this->once())->method('setOffset')
            ->with(9);

        $this->process->expects($this->once())->method('setComplete')
            ->with('100.0%');

        $this->process->expects($this->once())->method('setLastFeedName')
            ->with('doofinder-feed.xml');

        $this->process->expects($this->once())->method('setMessage')
            ->with('Last process successfully completed. Now waiting for new schedule.');

        $this->process->expects($this->once())->method('save');

        $this->helper->runProcess($this->process);
    }

    /**
     * Test runProcess() method
     *
     * @return void
     */
    public function testRunProcessRenameError()
    {
        $this->fetcher->method('isDone')->willReturn(true);

        $this->driver->expects($this->once())->method('rename')
            ->with('/tmp/doofinder-feed.xml.tmp', '/media/doofinder-feed.xml', $this->driver)
            ->willReturn(false);

        $this->process->expects($this->never())->method('setLastFeedName');

        $this->process->expects($this->once())->method('setMessage')
            ->with('#error#Cannot rename doofinder-feed.xml.tmp to doofinder-feed.xml');

        $this->process->expects($this->once())->method('setErrorStack')->with(1);

        $this->process->expects($this->once())->method('save');

        $this->helper->runProcess($this->process);
    }

    /**
     * Test isFeedFileExist() method
     *
     * @param  boolean $expected
     * @return void
     * @dataProvider providerTestIsFeedFileExist
     */
    public function testIsFeedFileExist($expected)
    {
        $this->directory->expects($this->once())->method('isExist')
            ->with('doofinder-default.xml')->willReturn($expected);

        $this->assertEquals(
            $expected,
            $this->helper->isFeedFileExist('default')
        );
    }

    /**
     * Data provider for testIsFeedFileExist() test
     *
     * @return array
     */
    public function providerTestIsFeedFileExist()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * Test getFeedFileUrl() method
     *
     * @param  string $storeCode
     * @param  string $passwordConfig
     * @param  string $password
     * @param  string $expected
     * @return void
     * @dataProvider providerTestGetFeedFileUrl
     */
    public function testGetFeedFileUrl($storeCode, $passwordConfig, $password, $expected)
    {
        $this->storeConfig->method('getStoreConfig')->with($storeCode)->willReturn(['password' => $passwordConfig]);

        $this->assertEquals(
            $expected,
            $this->helper->getFeedFileUrl($storeCode, $password)
        );
    }

    /**
     * Data provider for testGetFeedFileUrl() method
     *
     * @return array
     */
    public function providerTestGetFeedFileUrl()
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
