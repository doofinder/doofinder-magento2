<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\SearchEngines;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class ProcessStatus extends Action implements HttpGetActionInterface
{
    public const MENU_ID = 'Doofinder_Feed::searchEnginesProcessStatus';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index Processing Status constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::MENU_ID);

        return $resultPage;
    }
}
