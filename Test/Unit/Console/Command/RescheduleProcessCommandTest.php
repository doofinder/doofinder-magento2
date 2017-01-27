<?php

namespace Doofinder\Feed\Test\Unit\Console\Command;

/**
 * Class RecheduleProcessCommandTest
 *
 * @package Doofinder\Feed\Test\Unit\Console\Command
 */
class RescheduleProcessCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Doofinder\Feed\Console\Command\RescheduleProcessCommand
     */
    protected $_command;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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

        $this->_command = $this->_objectManager->getObject(
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
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester(
            $this->_command
        );

        $this->_schedule->expects($this->once())->method('updateProcess')->with($this->_store, true, true);

        $commandTester->execute(['store' => 'default']);
    }
}
