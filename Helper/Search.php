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
     * @var \Doofinder\Feed\Helper\SearchFilter
     */
    private $filterHelper;

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
     * @param \Doofinder\Feed\Helper\SearchFilter $filterHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Search\SearchClientFactory $searchFactory,
        \Doofinder\Feed\Search\ManagementClientFactory $dmaFactory,
        \Doofinder\Feed\Wrapper\ThrottleFactory $throttleFactory,
        \Doofinder\Feed\Helper\SearchFilter $filterHelper
    ) {
        $this->storeConfig = $storeConfig;
        $this->searchFactory = $searchFactory;
        $this->dmaFactory = $dmaFactory;
        $this->throttleFactory = $throttleFactory;
        $this->filterHelper = $filterHelper;
        parent::__construct($context);
    }

    /**
     * Perform a doofinder search on given key.
     *
     * @param string $queryText
     * @return array The array od product ids from first page.
     */
    public function performDoofinderSearch($queryText)
    {
        $hashId = $this->storeConfig->getHashId($this->getStoreCode());
        if (!$hashId) {
            return [];
        }
        $apiKey = $this->storeConfig->getApiKey();
        $limit = $this->storeConfig->getSearchRequestLimit($this->getStoreCode());

        $client = $this->searchFactory->create($hashId, $apiKey);

        try {
            // @codingStandardsIgnoreStart
            $results = $client->query(
                $queryText,
                null,
                [
                    'rpp' => $limit,
                    'transformer' => 'onlyid',
                    'filter' => $this->filterHelper->getFilters()
                ]
            );
            // @codingStandardsIgnoreEnd
        } catch (\Doofinder\Api\Search\Error $e) {
            $results = null;
            $this->_logger->critical($e->getMessage());
        }

        // Store objects
        $this->lastSearch = $client;
        $this->lastResults = $results;

        return $results ? $this->retrieveIds($results) : [];
    }

    /**
     * Retrieve ids from Doofinder results
     *
     * @param \Doofinder\Api\Search\Results $results
     * @return array
     */
    private function retrieveIds(\Doofinder\Api\Search\Results $results)
    {
        $ids = [];
        foreach ($results->getResults() as $result) {
            $ids[] = $result['id'];
        }

        return $ids;
    }

    /**
     * Fetch all results of last doofinder search
     *
     * @return array - The array of products ids from all pages
     */
    public function getAllResults()
    {
        if (!$this->lastResults) {
            return [];
        }

        $limit = $this->storeConfig->getSearchTotalLimit($this->getStoreCode());
        $ids = $this->retrieveIds($this->lastResults);

        while (count($ids) < $limit && ($results = $this->lastSearch->nextPage())) {
            $ids = array_merge($ids, $this->retrieveIds($results));
        }

        return $ids;
    }

    /**
     * Returns fetched results count
     *
     * @return integer
     */
    public function getResultsCount()
    {
        return $this->lastResults ? $this->lastResults->getProperty('total') : 0;
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
     * @return \Doofinder\Feed\Wrapper\Throttle
     * @throws \Magento\Framework\Exception\LocalizedException Search engine not exists.
     */
    private function getDoofinderSearchEngine()
    {
        if ($this->searchEngines === null) {
            $this->searchEngines = $this->getDoofinderSearchEngines($this->storeConfig->getApiKey());
        }

        // Prepare SearchEngine instance
        $hashId = $this->storeConfig->getHashId($this->getStoreCode());
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
        $result = $searchEngine->updateItems('product', array_values($items));

        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There was an error during Doofinder index items update.')
            );
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
        if (!$this->getDoofinderSearchEngine()->deleteType('product')) {
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
        if (!$this->getDoofinderSearchEngine()->addType('product')) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There was an error during Doofinder index creation')
            );
        }
    }

    /**
     * Get store id from dimensions
     *
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return integer|null
     */
    public function getStoreIdFromDimensions(array $dimensions)
    {
        foreach ($dimensions as $dimension) {
            if ($dimension->getName() == 'scope') {
                return $dimension->getValue();
            }
        }

        return null;
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
