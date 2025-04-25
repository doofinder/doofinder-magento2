<?php

namespace Doofinder\Feed\Service;

use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Errors\DoofinderFeedException;
use Doofinder\Feed\Errors\SearchEngineCreationException;
use Doofinder\Feed\Helper\Indexation;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\SearchEngineRepository;
use Exception;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Service for managing Doofinder search engines.
 */
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

    /**
     * Constructor.
     *
     * @param StoreConfig $storeConfig
     * @param SearchEngineRepository $searchEngineRepository
     * @param ManagementClientFactory $managementClientFactory
     */
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
     * Creates a search engine for a given store.
     *
     * @param StoreInterface $store The store for which the search engine is created.
     * @return array The response from the Doofinder API.
     * @throws SearchEngineCreationException If the search engine creation fails.
     */
    public function createSearchEngine(StoreInterface $store): array
    {
        $searchEngineData = $this->searchEngineRepository->getByStore($store);

        if (null === $searchEngineData->getStoreId()) {
            throw new SearchEngineCreationException('The Doofinder Store has not been created yet.');
        }

        $managementClient =
            $this->managementClientFactory->create(['apiType' => 'dooplugins']);

        $response =
            $managementClient->createSearchEngine($searchEngineData->jsonSerialize());

        $storeId = (int)$store->getId();

        $this->storeConfig->setHashId($response["hashid"], $storeId);
        $this->storeConfig->setIndexationStatus(
            ["status" => Indexation::DOOFINDER_INDEX_PROCESS_STATUS_STARTED],
            $storeId
        );
        return $response;
    }
}
