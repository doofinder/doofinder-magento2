<?php
declare(strict_types=1);


namespace Doofinder\Feed\ApiClient;

use Doofinder\Feed\Errors\BadRequest;
use Doofinder\Feed\Errors\IndexingInProgress;
use Doofinder\Feed\Errors\NotAllowed;
use Doofinder\Feed\Errors\NotFound;
use Doofinder\Feed\Errors\QuotaExhausted;
use Doofinder\Feed\Errors\ThrottledResponse;
use Doofinder\Feed\Errors\TypeAlreadyExists;
use Doofinder\Feed\Errors\WrongResponse;

class ManagementClient
{
    const ENDPOINT_SEARCH_ENGINES = '/api/v2/search_engines';

    /** @var Client */
    private $client;

    public function __construct(
        ClientFactory $clientFactory,
        string $apiKey = null,
        string $apiType = Client::MANAGEMENT_API
    ) {
        $this->client = $clientFactory->create(['apiKey' => $apiKey, 'apiType' => $apiType]);
    }

    /**
     * Request all user's search engines throttled
     * @see https://docs.doofinder.com/api/management/v2/#operation/search_engine_list
     *
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function listSearchEngines(): array
    {
        $response = $this->client->get(self::ENDPOINT_SEARCH_ENGINES);

        return json_decode($response, true);
    }

    public function createStore(array $storeData): array 
    {
        $response = $this->client->post('/plugins/create-store', $storeData);

        return json_decode($response, true);
    }

    /**
     * Creates a new search engine with the provided data
     * @see https://docs.doofinder.com/api/management/v2/#operation/search_engine_create
     *
     * @param array $searchEngine
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function createSearchEngine(array $searchEngine): array
    {
        $response = $this->client->post(self::ENDPOINT_SEARCH_ENGINES, $searchEngine);

        return json_decode($response, true);
    }

    /**
     * Schedules a task for processing all search engine's data sources.
     * @see https://docs.doofinder.com/api/management/v2/#operation/process
     *
     * @param string $hashId
     * @param string|null $callbackUrl
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function processSearchEngine(string $hashId, string $callbackUrl = null): array
    {
        $path = $this->getProcessSearchEnginePath($hashId);
        $response = $this->client->post($path, ['callback_url' => $callbackUrl]);

        return json_decode($response, true);
    }

    /**
     * Request a search engine details
     * @see https://docs.doofinder.com/api/management/v2/#operation/search_engine_show
     * @param string $hashId
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function getSearchEngine(string $hashid): array 
    {
        $url = $this->getSearchEnginePath($hashid);
        $response = $this->client->get($url);
        return json_decode($response, true);
    }

    /**
     * Creates a new index for a search engine
     * @see https://docs.doofinder.com/api/management/v2/#operation/index_create
     *
     * @param array $indice
     * @param string $hashId
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function createIndice(array $indice, string $hashId): array
    {
        $response = $this->client->post($this->getIndicesPath($hashId), $indice);

        return json_decode($response, true);
    }

    /**
     * Creates a list of items from the index in a single bulk operation.
     * @see https://docs.doofinder.com/api/management/v2/#operation/items_bulk_update
     *
     * @param array $items
     * @param string $hashId
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function createItemsInBulk(array $items, string $hashId, string $indice): array
    {
        $path = $this->getItemsBulkPath($hashId, $indice);
        $response = $this->client->post($path, $items);

        return json_decode($response, true);
    }

    /**
     * Updates a list of items from the index in a single bulk operation.
     * @see https://docs.doofinder.com/api/management/v2/#operation/items_bulk_update
     *
     * @param array $items
     * @param string $hashId
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function updateItemsInBulk(array $items, string $hashId, string $indice): array
    {
        $path = $this->getItemsBulkPath($hashId, $indice);
        $response = $this->client->post($path, $items);

        return json_decode($response, true);
    }

    /**
     * Deletes a list of items from the index in a single bulk operation.
     * @see https://docs.doofinder.com/api/management/v2/#operation/items_bulk_delete
     *
     * @param array $items
     * @param string $hashId
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function deleteItemsInBulk(array $items, string $hashId, string $indice): array
    {
        $path = $this->getItemsBulkPath($hashId, $indice);
        $response = $this->client->delete($path, $items);

        return json_decode($response, true);
    }

    /**
     * Returns search engine endpoint
     *
     * @param string $hashId
     * @return string
     */
    private function getSearchEnginePath(string $hashId): string
    {
        return self::ENDPOINT_SEARCH_ENGINES . "/{$hashId}";
    }

    /**
     * Returns search engine endpoint
     *
     * @param string $hashId
     * @return string
     */
    private function getProcessSearchEnginePath(string $hashId): string
    {
        return self::ENDPOINT_SEARCH_ENGINES . "/{$hashId}/_process";
    }

    /**
     * Returns indices endpoint
     *
     * @param string $hashId
     * @return string
     */
    private function getIndicesPath(string $hashId): string
    {
        return self::ENDPOINT_SEARCH_ENGINES . "/{$hashId}/indices";
    }

    /**
     * Returns items endpoint
     *
     * @param string $hashId
     * @param string $indice
     * @return string
     */
    private function getItemsBulkPath(string $hashId, string $indice): string
    {
        return self::ENDPOINT_SEARCH_ENGINES . "/{$hashId}/indices/{$indice}/items/_bulk";
    }
}
