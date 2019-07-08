<?php

namespace Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed;

/**
 * Test class for \Doofinder\Feed\Controller\Adminhtml\Feed\Log
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LogTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\Redirect
     */
    private $resultRedirect;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    private $pageTitle;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    private $resultPage;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Controller\Adminhtml\Feed\Log
     */
    private $controller;

    /**
     * Set up test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

        $this->resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirect->method('setPath')->willReturn($this->resultRedirect);

        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->method('create')->willReturn($this->resultRedirect);

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);
        $this->context->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->method('getRequest')->willReturn($this->request);

        $this->pageTitle = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfig->method('getTitle')->willReturn($this->pageTitle);

        $this->resultPage = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPage->method('getConfig')->willReturn($this->pageConfig);

        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactory->method('create')->willReturn($this->resultPage);

        $this->schedule = $this->getMockBuilder(\Doofinder\Feed\Helper\Schedule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataPersistor = $this->getMockBuilder(\Magento\Framework\App\Request\DataPersistorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store->method('getCode')->willReturn('sample');

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->method('getStore')
            ->with(1)->willReturn($this->store);

        $this->controller = $this->objectManager->getObject(
            \Doofinder\Feed\Controller\Adminhtml\Feed\Log::class,
            [
                'context' => $this->context,
                'resultPageFactory'   => $this->resultPageFactory,
                'schedule' => $this->schedule,
                'dataPersistor' => $this->dataPersistor,
                'storeManager' => $this->storeManager,
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
        $process = $this->getMockBuilder(\Doofinder\Feed\Model\Cron::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->schedule->expects($this->once())->method('getProcessByStoreCode')
            ->with('sample')->willReturn($process);

        $this->dataPersistor->expects($this->once())->method('set')
            ->with('doofinder_feed_process', $process);

        $this->request->method('getParam')->will($this->returnValueMap([
            ['store', null, 1],
        ]));

        $this->assertEquals($this->resultPage, $this->controller->execute());
    }

    /**
     * Test execute() method without process
     *
     * @return void
     */
    public function testExecuteWithoutProcess()
    {
        $this->dataPersistor->expects($this->never())->method('set');

        $this->request->method('getParam')->will($this->returnValueMap([
            ['store', null, 1],
        ]));

        $this->assertEquals($this->resultRedirect, $this->controller->execute());
    }

    /**
     * Test execute() method without store param
     *
     * @return void
     */
    public function testExecuteWithoutStore()
    {
        $this->dataPersistor->expects($this->never())->method('set');

        $this->assertEquals($this->resultPage, $this->controller->execute());
    }
}
