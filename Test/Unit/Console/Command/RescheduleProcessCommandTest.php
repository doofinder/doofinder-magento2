<?php

namespace Doofinder\Feed\Test\Unit\Console\Command;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class RecheduleProcessCommandTest
 *
 * @package Doofinder\Feed\Test\Unit\Console\Command
 */
class RescheduleProcessCommandTest extends BaseTestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $_store;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Doofinder\Feed\Console\Command\RescheduleProcessCommand
     */
    private $_command;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_store = $this->getMock(
            '\Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );

        $this->_storeManager = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->_storeManager->expects($this->once())->method('getStore')->with('default')->willReturn($this->_store);

        $this->_schedule = $this->getMock(
            '\Doofinder\Feed\Helper\Schedule',
            [],
            [],
            '',
            false
        );

        $this->_command = $this->objectManager->getObject(
            '\Doofinder\Feed\Console\Command\RescheduleProcessCommand',
            [
                'storeManager'  => $this->_storeManager,
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

        $this->_schedule->expects($this->once())->method('updateProcess')->with($this->_store, true, true);

        $commandTester->execute(['store' => 'default']);
    }
}
