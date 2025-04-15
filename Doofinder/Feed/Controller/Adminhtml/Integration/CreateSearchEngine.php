<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Service\SearchEngineService;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Webapi\Exception as WebapiException;


class CreateSearchEngine extends Action
{
    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var StoreConfig */
    protected $storeConfig;

    /** @var SearchEngineService */
    protected $searchEngineService;

    /** @var Escaper */
    protected $escaper;

    /**
     * CleanIntegration constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param StoreConfig $storeConfig
     * @param Context $context
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        StoreConfig $storeConfig,
        SearchEngineService $searchEngineService,
        Escaper $escaper,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeConfig = $storeConfig;
        $this->searchEngineService = $searchEngineService;
        $this->escaper = $escaper;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {

            $store = $this->storeConfig->getCurrentStore();

            $result = $this->searchEngineService->createSearchEngine($store);

            $resultJson->setData(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $resultJson->setData([
                'success' => false,
                'message' => $this->escaper->escapeHtml($e->getMessage()),
            ])->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
        }

        return $resultJson;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Doofinder_Feed::config');
    }
}
