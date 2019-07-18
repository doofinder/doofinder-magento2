<?php

namespace Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed;

/**
 * Test class for \Doofinder\Feed\Controller\Adminhtml\Feed\Generate
 */
class GenerateTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
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

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->schedule = $this->getMockBuilder(\Doofinder\Feed\Helper\Schedule::class)
            ->setMethods(['regenerateSchedule'])
            ->disableOriginalConstructor()
            ->getMock();

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
