<?php

namespace Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class LogTest
 * @package Doofinder\Feed\Test\Unit\Controller\Adminhtml\Feed
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LogTest extends BaseTestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\Redirect
     */
    private $_resultRedirect;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    private $_resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $_context;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    private $_pageTitle;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $_pageConfig;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    private $_resultPage;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $_resultPageFactory;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $_dataPersistor;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $_store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Doofinder\Feed\Controller\Adminhtml\Feed\Log
     */
    private $_controller;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        parent::setUp();

        $this->_resultRedirect = $this->getMock(
            '\Magento\Backend\Model\View\Result\Redirect',
            [],
            [],
            '',
            false
        );
        $this->_resultRedirect->method('setPath')->willReturn($this->_resultRedirect);

        $this->_resultRedirectFactory = $this->getMock(
            '\Magento\Backend\Model\View\Result\RedirectFactory',
            [],
            [],
            '',
            false
        );
        $this->_resultRedirectFactory->method('create')->willReturn($this->_resultRedirect);

        $this->_messageManager = $this->getMock(
            '\Magento\Framework\Message\ManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->_request = $this->getMock(
            '\Magento\Framework\App\RequestInterface',
            [],
            [],
            '',
            false
        );

        $this->_context = $this->getMock(
            '\Magento\Backend\App\Action\Context',
            [],
            [],
            '',
            false
        );
        $this->_context->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_context->method('getMessageManager')->willReturn($this->_messageManager);
        $this->_context->method('getRequest')->willReturn($this->_request);

        $this->_pageTitle = $this->getMock(
            'Magento\Framework\View\Page\Title',
            [],
            [],
            '',
            false
        );

        $this->_pageConfig = $this->getMock(
            '\Magento\Framework\View\Page\Config',
            [],
            [],
            '',
            false
        );
        $this->_pageConfig->method('getTitle')->willReturn($this->_pageTitle);

        $this->_resultPage = $this->getMock(
            '\Magento\Framework\View\Result\Page',
            [],
            [],
            '',
            false
        );
        $this->_resultPage->method('getConfig')->willReturn($this->_pageConfig);

        $this->_resultPageFactory = $this->getMock(
            '\Magento\Framework\View\Result\PageFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_resultPageFactory->method('create')->willReturn($this->_resultPage);

        $this->_schedule = $this->getMock(
            '\Doofinder\Feed\Helper\Schedule',
            [],
            [],
            '',
            false
        );

        $this->_dataPersistor = $this->getMock(
            '\Magento\Framework\App\Request\DataPersistorInterface',
            [],
            [],
            '',
            false
        );

        $this->_store = $this->getMock(
            '\Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $this->_store->method('getCode')->willReturn('sample');

        $this->_storeManager = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->_storeManager->method('getStore')
            ->with(1)->willReturn($this->_store);

        $this->_controller = $this->objectManager->getObject(
            '\Doofinder\Feed\Controller\Adminhtml\Feed\Log',
            [
                'context' => $this->_context,
                'resultPageFactory'   => $this->_resultPageFactory,
                'schedule' => $this->_schedule,
                'dataPersistor' => $this->_dataPersistor,
                'storeManager' => $this->_storeManager,
            ]
        );
    }

    /**
     * Test execute()
     */
    public function testExecute()
    {
        $process = $this->getMock(
            '\Doofinder\Feed\Model\Cron',
            [],
            [],
            '',
            false
        );

        $this->_schedule->expects($this->once())->method('getProcessByStoreCode')
            ->with('sample')->willReturn($process);

        $this->_dataPersistor->expects($this->once())->method('set')
            ->with('doofinder_feed_process', $process);

        $this->_request->method('getParam')->will($this->returnValueMap([
            ['store', null, 1],
        ]));

        $this->assertEquals($this->_resultPage, $this->_controller->execute());
    }

    /**
     * Test execute() without process
     */
    public function testExecuteWithoutProcess()
    {
        $this->_dataPersistor->expects($this->never())->method('set');

        $this->_request->method('getParam')->will($this->returnValueMap([
            ['store', null, 1],
        ]));

        $this->assertEquals($this->_resultRedirect, $this->_controller->execute());
    }

    /**
     * Test execute() without store param
     */
    public function testExecuteWithoutStore()
    {
        $this->_dataPersistor->expects($this->never())->method('set');

        $this->assertEquals($this->_resultPage, $this->_controller->execute());
    }
}
