<?php

namespace Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class GenerateTest
 * @package Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed
 */
class GenerateTest extends BaseTestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $_context;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $_resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    private $_resultJson;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Doofinder\Feed\Controller\Adminhtml\Feed\Generate
     */
    private $_controller;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->_context = $this->getMock(
            '\Magento\Backend\App\Action\Context',
            [],
            [],
            '',
            false
        );

        $this->_resultJsonFactory = $this->getMock(
            '\Magento\Framework\Controller\Result\JsonFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_resultJson = $this->getMock(
            '\Magento\Framework\Controller\Result\Json',
            ['setData'],
            [],
            '',
            false
        );

        $this->_schedule = $this->getMock(
            '\Doofinder\Feed\Helper\Schedule',
            ['regenerateSchedule'],
            [],
            '',
            false
        );

        $this->_resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->_resultJson);

        $this->_schedule->expects($this->once())
            ->method('regenerateSchedule');

        $this->_resultJson->expects($this->once())
            ->method('setData')
            ->with(['message' => \Doofinder\Feed\Controller\Adminhtml\Feed\Generate::FEED_GENERATION_MESSAGE]);

        $this->_controller = $this->objectManager->getObject(
            '\Doofinder\Feed\Controller\Adminhtml\Feed\Generate',
            [
                'context'       => $this->_context,
                'resultJsonFactory'   => $this->_resultJsonFactory,
                'schedule' => $this->_schedule,
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
