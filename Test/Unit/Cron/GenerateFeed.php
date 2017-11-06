<?php

namespace Doofinder\Feed\Test\Unit\Cron;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class GenerateFeedTest
 * @package Doofinder\Feed\Test\Unit\Helper
 */
class GenerateFeedTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $_process;

    /**
     * @var \Doofinder\Feed\Cron\GenerateFeed
     */
    private $_cron;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

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

        $this->_cron = $this->objectManager->getObject(
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
