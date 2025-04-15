<?php

namespace Doofinder\Feed\Service;

use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Helper\Indexation;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\SearchEngineOptionsStruct;
use Doofinder\Feed\Model\Data\SearchEngineStruct;
use Doofinder\Feed\Model\SearchEngineRepository;
use Exception;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;

class SearchEngineService
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var SearchEngineRepository
     */
    private $searchEngineRepository;

    /**
     * @var ManagementClientFactory
     */
    private $managementClientFactory;


    public function __construct(
        StoreConfig $storeConfig,
        SearchEngineRepository $searchEngineRepository,
        ManagementClientFactory $managementClientFactory
    ) {
        $this->storeConfig = $storeConfig;
        $this->searchEngineRepository = $searchEngineRepository;
        $this->managementClientFactory = $managementClientFactory;
    }


    /**
     * Get search engines for a store group unique by language and currency
     * The store_id field refers to store_view's id.
     *
     * @param Group $storeGroup
     * @return bool
     */
    public function createSearchEngine(StoreInterface $store): array
    {
        try {

            $searchEngineData = $this->searchEngineRepository->getByStore($store);

            if (null === $searchEngineData->getStoreId()) {
                throw new Exception('The Doofinder Store has not been created yet.');
            }

            $managementClient = $this->managementClientFactory->create(['apiType' => 'dooplugins']);

            $response = $managementClient->createSearchEngine($searchEngineData);

            $storeId = (int)$store->getId();

            $this->storeConfig->setHashId($response["hashid"], $storeId);
            $this->storeConfig->setIndexationStatus(["status" => Indexation::DOOFINDER_INDEX_PROCESS_STATUS_STARTED], $storeId);
            return $response;
        } catch (Exception $e) {
            throw new Exception('Error creating search engine: ' . $e->getMessage());
        }
    }
}
