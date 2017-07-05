<?php

namespace Doofinder\Feed\Test\Unit\Console\Command;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class RunProcessCommandTest
 *
 * @package Doofinder\Feed\Test\Unit\Console\Command
 */
class RunProcessCommandTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\CronFactory
     */
    private $_cronFactory;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $_process;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Doofinder\Feed\Console\Command\RunProcessCommand
     */
    private $_command;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_process = $this->getMock(
            '\Doofinder\Feed\Model\Cron',
            [],
            [],
            '',
            false
        );
        $this->_process->method('load')->willReturn($this->_process);

        $this->_cronFactory = $this->getMock(
            '\Doofinder\Feed\Model\CronFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_cronFactory->method('create')->willReturn($this->_process);

        $this->_schedule = $this->getMock(
            '\Doofinder\Feed\Helper\Schedule',
            [],
            [],
            '',
            false
        );

        $this->_command = $this->objectManager->getObject(
            '\Doofinder\Feed\Console\Command\RunProcessCommand',
            [
                'cronFactory'  => $this->_cronFactory,
                'schedule' => $this->_schedule,
            ]
        );
    }

    /**
     * Test execute() method
     */
    public function testExecute()
    {
        // @codingStandardsIgnoreStart
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester(
            $this->_command
        );
        // @codingStandardsIgnoreEnd

        $this->_schedule->expects($this->once())->method('runProcess')->with($this->_process);

        $commandTester->execute(['store' => 'default']);
    }
}
