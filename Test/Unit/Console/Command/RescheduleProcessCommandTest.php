<?php

namespace Doofinder\Feed\Test\Unit\Console\Command;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Console\Command\RescheduleProcessCommand
 */
class RescheduleProcessCommandTest extends BaseTestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Doofinder\Feed\Console\Command\RescheduleProcessCommand
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

        $this->store = $this->getMock(
            \Magento\Store\Model\Store::class,
            [],
            [],
            '',
            false
        );

        $this->storeManager = $this->getMock(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            [],
            '',
            false
        );
        $this->storeManager->expects($this->once())->method('getStore')->with('default')->willReturn($this->store);

        $this->schedule = $this->getMock(
            \Doofinder\Feed\Helper\Schedule::class,
            [],
            [],
            '',
            false
        );

        $this->command = $this->objectManager->getObject(
            \Doofinder\Feed\Console\Command\RescheduleProcessCommand::class,
            [
                'storeManager'  => $this->storeManager,
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

        $this->schedule->expects($this->once())->method('updateProcess')->with($this->store, true, true);

        $commandTester->execute(['store' => 'default']);
    }
}
