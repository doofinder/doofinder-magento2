<?php

namespace Doofinder\Feed\Test\Unit\Helper;

/**
 * Test class for \Doofinder\Feed\Helper\Schedule
 */
class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

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
    protected $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    protected $_generator;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Fetcher
     */
    protected $_fetcher;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezone;

    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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
            '\Doofinder\Feed\Model\Generator\Component\Fetcher',
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

        $this->_timezone = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            [],
            [],
            '',
            false
        );
        $this->_timezone->method('getConfigTimezone')->willReturn('America/Los_Angeles');

        $this->_helper = $this->_objectManager->getObject(
            '\Doofinder\Feed\Helper\Schedule',
            [
                'filesystem' => $this->_filesystem,
                'generatorFactory' => $this->_generatorFactory,
                'timezone' => $this->_timezone,
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
}