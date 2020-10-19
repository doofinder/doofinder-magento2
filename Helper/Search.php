<?php

namespace Doofinder\Feed\Helper;

/**
 * Search helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Search extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Search\SearchClientFactory
     */
    private $searchFactory;

    /**
     * @var \Doofinder\Feed\Search\ManagementClientFactory
     */
    private $dmaFactory;

    /**
     * @var \Doofinder\Feed\Wrapper\Throttle
     */
    private $throttleFactory;

    /**
     * @var \Doofinder\Api\Management\SearchEngine[]
     */
    private $searchEngines = null;

    /**
     * @var \Doofinder\Api\Search\Client|null
     */
    private $lastSearch = null;

    /**
     * @var \Doofinder\Api\Search\Results|null
     */
    private $lastResults = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Search\SearchClientFactory $searchFactory
     * @param \Doofinder\Feed\Search\ManagementClientFactory $dmaFactory
     * @param \Doofinder\Feed\Wrapper\ThrottleFactory $throttleFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Search\SearchClientFactory $searchFactory,
        \Doofinder\Feed\Search\ManagementClientFactory $dmaFactory,
        \Doofinder\Feed\Wrapper\ThrottleFactory $throttleFactory
    ) {
        $this->storeConfig = $storeConfig;
        $this->searchFactory = $searchFactory;
        $this->dmaFactory = $dmaFactory;
        $this->throttleFactory = $throttleFactory;
        parent::__construct($context);
    }

    /**
     * Perform a doofinder search on given key.
     *
     * @param string $queryText
     * @param array $options
     * @return \Doofinder\Api\Search\Results
     */
    public function performDoofinderSearch($queryText, array $options = [])
    {
        $hashId = $this->storeConfig->getHashId($this->getStoreCode());
        $apiKey = $this->storeConfig->getApiKey();

        $client = $this->searchFactory->create($hashId, $apiKey);
        try {
            $results = $client->query(
                $queryText,
                null,
                $options
            );
        } catch (\Doofinder\Api\Search\Error $e) {
            $results = null;
            $this->_logger->critical($e->getMessage());
        }

        // Store objects
        $this->lastSearch = $client;
        $this->lastResults = $results;

        return $results;
    }

    /**
     * Returns current store code
     *
     * @return string
     */
    private function getStoreCode()
    {
        return $this->storeConfig->getStoreCode();
    }

    /**
     * Initialize search engines
     *
     * @param  string $apiKey
     * @return array
     */
    public function getDoofinderSearchEngines($apiKey)
    {
        // Create DoofinderManagementApi instance
        $doofinderApi = $this->throttleFactory->create([
            'obj' => $this->dmaFactory->create($apiKey)
        ]);

        $searchEngines = [];
        foreach ($doofinderApi->getSearchEngines() as $searchEngine) {
            $searchEngines[$searchEngine->hashid] = $searchEngine;
        }

        return $searchEngines;
    }

    /**
     * Get Doofinder Search Engine
     *
     * @return \Doofinder\Feed\Wrapper\Throttle|null
     * @throws \Magento\Framework\Exception\LocalizedException Search engine not exists.
     */
    private function getDoofinderSearchEngine()
    {
        if ($this->searchEngines === null) {
            $this->searchEngines = $this->getDoofinderSearchEngines($this->storeConfig->getApiKey());
        }

        $code = $this->getStoreCode();

        // Prepare SearchEngine instance
        $hashId = $this->storeConfig->getHashId($code);
        if (!empty($this->searchEngines[$hashId])) {
            return $this->throttleFactory->create(['obj' => $this->searchEngines[$hashId]]);
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __('Search engine with HashID %1 doesn\'t exists. Please, check your configuration.', $hashId)
        );
    }

    /**
     * Update Doofinder items
     *
     * @param  array $items
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException Index items update error.
     */
    public function updateDoofinderItems(array $items)
    {
        $searchEngine = $this->getDoofinderSearchEngine();
        if ($searchEngine) {
            $result = $searchEngine->updateItems('product', array_values($items));

            if (!$result) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('There was an error during Doofinder index items update.')
                );
            }
        }
    }

    /**
     * Delete Doofinder items
     *
     * @param  array $items
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException Index items delete error.
     */
    public function deleteDoofinderItems(array $items)
    {
        $searchEngine = $this->getDoofinderSearchEngine();
        if (!$searchEngine) {
            return false;
        }
        $result = $searchEngine->deleteItems('product', array_map(function ($item) {
            return $item['id'];
        }, $items));

        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There was an error during Doofinder index items deletion.')
            );
        }

        if (!empty($result['errors'])) {
            $this->_logger->warning(__(
                'Following items could not be deleted from Doofinder index: %1.',
                implode(', ', $result['errors'])
            ));
            return false;
        }

        return true;
    }

    /**
     * Delete Doofinder index
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException Index deletion error.
     */
    public function deleteDoofinderIndex()
    {
        $searchEngine = $this->getDoofinderSearchEngine();
        if ($searchEngine && !$searchEngine->deleteType('product')) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There was an error during Doofinder index deletion')
            );
        }
    }

    /**
     * Create Doofinder index
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException Index creation error.
     */
    public function createDoofinderIndex()
    {
        $searchEngine = $this->getDoofinderSearchEngine();
        if ($searchEngine && !$searchEngine->addType('product')) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There was an error during Doofinder index creation')
            );
        }
    }

    /**
     * Get search results banner data
     *
     * @return array|null
     */
    public function getDoofinderBannerData()
    {
        if ($this->lastResults) {
            return $this->lastResults->getProperty('banner');
        }

        return null;
    }
}
