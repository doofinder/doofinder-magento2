<?php

namespace Doofinder\Feed\Test\Unit\Model\Cron;

/**
 * Class ScheduleTest
 * @package Doofinder\Feed\Test\Unit\Model\Cron
 */
class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Cron\Model\ScheduleFactory
     */
    protected $_scheduleFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneMock;

    /**
     * @var \Magento\Cron\Model\Schedule
     */
    protected $_scheduleMock;

    /**
     * @var \Doofinder\Feed\Model\Cron\Schedule
     */
    protected $_model;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_scheduleFactoryMock = $this->getMock(
            '\Magento\Cron\Model\ScheduleFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_timezoneMock = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            [],
            [],
            '',
            false
        );

        $this->_scheduleMock = $this->getMock(
            '\Magento\Cron\Model\Schedule',
            ['create', 'setJobCode', 'setStatus', 'setCreatedAt', 'setScheduledAt', 'save'],
            [],
            '',
            false
        );

        $this->_scheduleFactoryMock->expects($this->once())
            ->method('create')
            ->wilLReturn($this->_scheduleMock);

        $this->_scheduleMock->expects($this->once())
            ->method('setJobCode')
            ->with(\Doofinder\Feed\Model\Cron\Schedule::JOB_CODE)
            ->willReturnSelf();

        $this->_scheduleMock->expects($this->once())
            ->method('setStatus')
            ->with(\Magento\Cron\Model\Schedule::STATUS_PENDING)
            ->willReturnSelf();

        $this->_scheduleMock->expects($this->once())
            ->method('setCreatedAt')
            ->willReturnSelf();

        $this->_scheduleMock->expects($this->once())
            ->method('setScheduledAt')
            ->willReturnSelf();

        $this->_model = $this->_objectManager->getObject(
            '\Doofinder\Feed\Model\Cron\Schedule',
            [
                'scheduleFactory' => $this->_scheduleFactoryMock,
                'timezone' => $this->_timezoneMock,
            ]
        );
    }

    /**
     * Test generateScheduleNow()
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGenerateScheduleNow()
    {
        $this->_model->generateScheduleNow();
    }
}
