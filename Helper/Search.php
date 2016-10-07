<?php

namespace Doofinder\Feed\Helper;

class Search extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Doofinder\Api\Search\ClientFactory
     */
    protected $_searchFactory;

    /**
     * @var \Doofinder\Api\Search\Client|null
     */
    protected $_lastSearch = null;

    /**
     * @var \Doofinder\Api\Search\Results|null
     */
    protected $_lastResults = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Api\Search\ClientFactory $searchFactory
    ) {
        $this->_storeConfig = $storeConfig;
        $this->_searchFactory = $searchFactory;
        parent::__construct($context);
    }

    /**
     * Perform a doofinder search on given key.
     *
     * @param string $queryText
     * @param int $limit
     * @param int $offset
     *
     * @return array - The array od product ids from first page
     */
    public function performDoofinderSearch($queryText)
    {
        $hashId = $this->_storeConfig->getHashId($this->getStoreCode());
        $apiKey = $this->_storeConfig->getApiKey();
        $limit = $this->_storeConfig->getSearchRequestLimit($this->getStoreCode());

        $client = $this->_searchFactory->create(['hashid' => $hashId, 'api_key' => $apiKey]);
        $results = $client->query($queryText, null, ['rpp' => $limit, 'transformer' => 'onlyid', 'filter' => []]);

        // Store objects
        $this->_lastSearch = $client;
        $this->_lastResults = $results;

        return $this->retrieveIds($results);
    }

    /**
     * Retrieve ids from Doofinder results
     *
     * @param \Doofinder\Api\Search\Results $results
     * @return array
     */
    protected function retrieveIds(\Doofinder\Api\Search\Results $results)
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
        $limit = $this->_storeConfig->getSearchTotalLimit($this->getStoreCode());
        $ids = $this->retrieveIds($this->_lastResults);

        while (count($ids) < $limit && ($results = $this->_lastSearch->nextPage())) {
            $ids = array_merge($ids, $this->retrieveIds($results));
        }

        return $ids;
    }

    /**
     * Returns fetched results count
     *
     * @return int
     */
    public function getResultsCount()
    {
        return $this->_lastResults->getProperty('total');
    }

    /**
     * Returns current store code
     *
     * @return string
     */
    protected function getStoreCode()
    {
        return $this->_storeConfig->getStoreCode();
    }
}
