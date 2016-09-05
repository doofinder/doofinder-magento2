<?php

namespace Doofinder\Feed\Test\Unit\Cron;

/**
 * Class GenerateFeedTest
 * @package Doofinder\Feed\Test\Unit\Helper
 */
class GenerateFeedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    protected $_process;

    /**
     * @var \Doofinder\Feed\Cron\GenerateFeed
     */
    protected $_cron;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_schedule = $this->getMock(
            '\Doofinder\Feed\Helper\Schedule',
            [],
            [],
            '',
            false
        );

        $this->_process = $this->getMock(
            '\Doofinder\Feed\Model\Cron',
            [],
            [],
            '',
            false
        );

        $this->_cron = $this->_objectManager->getObject(
            '\Doofinder\Feed\Cron\GenerateFeed',
            [
                'schedule'  => $this->_schedule,
            ]
        );
    }

    /**
     * Test execute() method
     */
    public function testExecute()
    {
        $this->_schedule->expects($this->once())->method('getActiveProcess')->willReturn($this->_process);
        $this->_schedule->expects($this->once())->method('runProcess')->with($this->_process);

        $this->_cron->execute();
    }
}
