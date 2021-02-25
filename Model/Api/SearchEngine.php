<?php

namespace Doofinder\Feed\Model\Api;

use Doofinder\Management\ManagementClientFactory;
use Doofinder\Management\ManagementClient;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * Class SearchEngine
 * The class responsible for communicating between Magento and Doofinder API Library
 */
class SearchEngine
{
    /**
     * @var ManagementClientFactory
     */
    private $managementClientFactory;

    /**
     * @var ManagementClient
     */
    private $managementClient;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * SearchEngine constructor.
     * @param ManagementClientFactory $managementClientFactory
     * @param StoreConfig $storeConfig
     */
    public function __construct(
        ManagementClientFactory $managementClientFactory,
        StoreConfig $storeConfig
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->storeConfig = $storeConfig;
    }

    /**
     * Get created Search Engines in Doofinder API
     * @param string|null $apiKey
     * @return array
     */
    public function getSearchEngines($apiKey = null)
    {
        $searchEngines = [];
        foreach ($this->getClient($apiKey)->listSearchEngines() as $searchEngine) {
            $searchEngines[$searchEngine->hashid] = $searchEngine;
        }
        return $searchEngines;
    }

    /**
     * Init Doofinder Management from Doofinder API Library
     * @param null|string $apiKey
     * @return ManagementClient
     */
    public function getClient($apiKey = null)
    {
        if (!$this->managementClient) {
            if ($apiKey) {
                $host = $this->storeConfig->getManagementServerFromRequest();
            }
            if ($apiKey == null) {
                $apiKey = $this->storeConfig->getApiKey();
            }
            $this->managementClient = $this->managementClientFactory->create([
                'host' => $host ?? $this->storeConfig->getManagementServer(),
                'token' => $apiKey
            ]);
        }
        return $this->managementClient;
    }
}
