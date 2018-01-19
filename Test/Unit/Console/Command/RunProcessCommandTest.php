<?php

namespace Doofinder\Feed\Test\Unit\Console\Command;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Console\Command\RunProcessCommand
 */
class RunProcessCommandTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\CronFactory
     */
    private $cronFactory;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $process;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Doofinder\Feed\Console\Command\RunProcessCommand
     */
    private $command;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->process = $this->getMock(
            \Doofinder\Feed\Model\Cron::class,
            [],
            [],
            '',
            false
        );
        $this->process->method('load')->willReturn($this->process);

        $this->cronFactory = $this->getMock(
            \Doofinder\Feed\Model\CronFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->cronFactory->method('create')->willReturn($this->process);

        $this->schedule = $this->getMock(
            \Doofinder\Feed\Helper\Schedule::class,
            [],
            [],
            '',
            false
        );

        $this->command = $this->objectManager->getObject(
            \Doofinder\Feed\Console\Command\RunProcessCommand::class,
            [
                'cronFactory'  => $this->cronFactory,
                'schedule' => $this->schedule,
            ]
        );
    }

    /**
     * Test execute() method
     *
     * @return void
     */
    public function testExecute()
    {
        // @codingStandardsIgnoreStart
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester(
            $this->command
        );
        // @codingStandardsIgnoreEnd

        $this->schedule->expects($this->once())->method('runProcess')->with($this->process);

        $commandTester->execute(['store' => 'default']);
    }
}
