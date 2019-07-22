<?php

namespace Doofinder\Feed\Test\Unit\Console\Command;

/**
 * Test class for \Doofinder\Feed\Console\Command\RescheduleProcessCommand
 */
class RescheduleProcessCommandTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
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

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())->method('getStore')->with('default')->willReturn($this->store);

        $this->schedule = $this->getMockBuilder(\Doofinder\Feed\Helper\Schedule::class)
            ->disableOriginalConstructor()
            ->getMock();

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
