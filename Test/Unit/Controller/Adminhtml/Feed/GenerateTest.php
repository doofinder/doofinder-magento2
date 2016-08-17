<?php

namespace Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed;

/**
 * Class GenerateTest
 * @package Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed
 */
class GenerateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $_contextMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $_resultJsonMock;

    /**
     * @var \Doofinder\Feed\Model\Cron\ScheduleFactory
     */
    protected $_scheduleFactoryMock;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_scheduleMock;

    /**
     * @var \Doofinder\Feed\Controller\Adminhtml\Feed\Generate
     */
    protected $_controller;

    /**
     *
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_contextMock = $this->getMock(
            '\Magento\Backend\App\Action\Context',
            [],
            [],
            '',
            false
        );

        $this->_resultJsonFactoryMock = $this->getMock(
            '\Magento\Framework\Controller\Result\JsonFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_resultJsonMock = $this->getMock(
            '\Magento\Framework\Controller\Result\Json',
            ['setData'],
            [],
            '',
            false
        );

        $this->_scheduleMock = $this->getMock(
            '\Doofinder\Feed\Helper\Schedule',
            ['regenerateSchedule'],
            [],
            '',
            false
        );

        $this->_resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_resultJsonMock);

        $this->_scheduleMock->expects($this->once())
            ->method('regenerateSchedule');

        $this->_resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(['message' => \Doofinder\Feed\Controller\Adminhtml\Feed\Generate::FEED_GENERATION_MESSAGE]);

        $this->_controller = $this->_objectManager->getObject(
            '\Doofinder\Feed\Controller\Adminhtml\Feed\Generate',
            [
                'context'       => $this->_contextMock,
                'resultJsonFactory'   => $this->_resultJsonFactoryMock,
                'schedule' => $this->_scheduleMock,
            ]
        );
    }

    /**
     * Test execute()
     */
    public function testExecute()
    {
        $this->_controller->execute();
    }

}
