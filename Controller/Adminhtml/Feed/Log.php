<?php

namespace Doofinder\Feed\Controller\Adminhtml\Feed;

/**
 * Class Log
 *
 * @package Doofinder\Feed\Controller\Adminhtml\Feed
 */
class Log extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $_resultPageFactory = false;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $_dataPersistor;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_dataPersistor = $dataPersistor;
        $this->_schedule = $schedule;
        $this->_storeManager = $storeManager;
    }

    public function execute()
    {
        if ($storeId = $this->getRequest()->getParam('store')) {
            $store = $this->_storeManager->getStore($storeId);
            $process = $this->_schedule->getProcessByStoreCode($store->getCode());
            if (!$process) {
                $this->messageManager->addError(__('Feed process does not exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('admin');
            }

            $this->_dataPersistor->set('doofinder_feed_process', $process);
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Doofinder Feed Log'));

        return $resultPage;
    }
}
