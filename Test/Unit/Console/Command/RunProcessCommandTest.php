<?php

namespace Doofinder\Feed\Test\Unit\Console\Command;

/**
 * Class RunProcessCommandTest
 *
 * @package Doofinder\Feed\Test\Unit\Console\Command
 */
class RunProcessCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Doofinder\Feed\Model\CronFactory
     */
    protected $_cronFactory;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    protected $_process;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Doofinder\Feed\Console\Command\RunProcessCommand
     */
    protected $_command;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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

        $this->_command = $this->_objectManager->getObject(
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
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester(
            $this->_command
        );

        $this->_schedule->expects($this->once())->method('runProcess')->with($this->_process);

        $commandTester->execute(['store' => 'default']);
    }
}
