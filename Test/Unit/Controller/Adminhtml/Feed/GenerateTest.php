<?php

namespace Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Controller\Adminhtml\Feed\Generate
 */
class GenerateTest extends BaseTestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    private $resultJson;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Doofinder\Feed\Controller\Adminhtml\Feed\Generate
     */
    private $controller;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            [],
            [],
            '',
            false
        );

        $this->resultJsonFactory = $this->getMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->resultJson = $this->getMock(
            \Magento\Framework\Controller\Result\Json::class,
            ['setData'],
            [],
            '',
            false
        );

        $this->schedule = $this->getMock(
            \Doofinder\Feed\Helper\Schedule::class,
            ['regenerateSchedule'],
            [],
            '',
            false
        );

        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->schedule->expects($this->once())
            ->method('regenerateSchedule');

        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(['message' => \Doofinder\Feed\Controller\Adminhtml\Feed\Generate::FEED_GENERATION_MESSAGE]);

        $this->controller = $this->objectManager->getObject(
            \Doofinder\Feed\Controller\Adminhtml\Feed\Generate::class,
            [
                'context'       => $this->context,
                'resultJsonFactory'   => $this->resultJsonFactory,
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
        $this->controller->execute();
    }
}
