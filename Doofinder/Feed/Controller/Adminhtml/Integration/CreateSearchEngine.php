<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\Indexation;
use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;

class CreateSearchEngine extends Action
{
    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var StoreConfig */
    protected $storeConfig;

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
        Escaper $escaper,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeConfig = $storeConfig;
        $this->escaper = $escaper;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $store = $this->storeConfig->getCurrentStore();

        $storeGroupId = (int)$store->getStoreGroupId();
        $installationId = $this->storeConfig->getValueFromConfig(StoreConfig::DISPLAY_LAYER_INSTALLATION_ID, ScopeInterface::SCOPE_GROUP, $storeGroupId);

        $language = $this->storeConfig->getLanguageFromStore($store);
        $currency = strtoupper($store->getCurrentCurrency()->getCode());

        $storeId = (int)$store->getId();
        $baseUrl = $store->getBaseUrl();

        // store_id field refers to store_view's id.
        $searchEngineConfig = [
            "name" => $store->getGroup()->getName() . ' - ' . $store->getName(),
            "language" => $language,
            "currency" => $currency,
            "site_url" => $baseUrl,
            "callback_url" => $baseUrl . 'doofinderfeed/setup/processCallback?storeId=' . $storeId,
            "options" => [
                "store_id" => $storeId,
                "index_url" => $baseUrl . 'rest/' . $store->getCode() . '/V1/'
            ],
            "store_id" => $installationId
        ];
        try {
            $response =  $this->storeConfig->createSearchEngine($searchEngineConfig);

            $this->storeConfig->setHashId($response["hashid"], $storeId);

            $status = ["status" => Indexation::DOOFINDER_INDEX_PROCESS_STATUS_STARTED];
            $this->storeConfig->setIndexationStatus($status, $storeId);
            $resultJson->setData(['result' => $response]);
        } catch (Exception $e) {

            $resultJson->setData([
                'result' => false,
                'error' => $this->escaper->escapeHtml("Error creating search engine in Doofinder: " . $e->getMessage()),
            ])->setHttpResponseCode(400);
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
