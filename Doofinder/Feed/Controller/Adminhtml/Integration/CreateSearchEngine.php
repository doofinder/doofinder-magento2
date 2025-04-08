<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\SearchEngine;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class CreateSearchEngine extends Action
{
    /** @var JsonFactory */
    private $resultJsonFactory;

    protected $searchEngine;

    private $logger;

    /**
     * CleanIntegration constructor.
     *
     * @param SearchEngine $searchEngine
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        SearchEngine $searchEngine,
        LoggerInterface $logger,

        Context $context
    ) {
        $this->resultFactory = $resultJsonFactory;
        $this->searchEngine = $searchEngine;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute() {}

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Doofinder_Feed::config');
    }
}
