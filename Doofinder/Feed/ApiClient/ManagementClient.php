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
    private const ENDPOINT_SEARCH_ENGINES = '/api/v2/search_engines';
    private const ENDPOINT_UPDATE_ON_SAVE = '/item';

    /** @var Client */
    private $client;

    /**
     * ManagementClient constructor.
     *
     * @param ClientFactory $clientFactory
     * @param string $apiKey
     * @param string $apiType
     */
    public function __construct(
        ClientFactory $clientFactory,
        string $apiKey = null,
        string $apiType = Client::MANAGEMENT_API
    ) {
        $this->client = $clientFactory->create(['apiKey' => $apiKey, 'apiType' => $apiType]);
    }

    /**
     * Request all user's search engines throttled
     *
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
     * @throws \Exception
     */
    public function listSearchEngines(): array
    {
        $response = $this->client->get(self::ENDPOINT_SEARCH_ENGINES);

        return json_decode($response, true);
    }

    /**
     * Creates the store structure in Doofinder
     *
     * @param array $storeData
     * @return array
     */
    public function createStore($storeData): array
    {
        $response = $this->client->post('/install', $storeData);

        return json_decode($response, true);
    }

    /**
     * Creates a new search engine with the provided data
     *
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
     * @throws \Exception
     */
    public function createSearchEngine(array $searchEngine): array
    {
        $response = $this->client->post("/install/search-engines", $searchEngine);

        return json_decode($response, true);
    }

    /**
     * Schedules a task for processing all search engine's data sources.
     *
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
     * @throws \Exception
     */
    public function processSearchEngine(string $hashId, string $callbackUrl = null): array
    {
        $path = $this->getProcessSearchEnginePath($hashId);
        $response = $this->client->post($path, ['callback_url' => $callbackUrl]);

        return json_decode($response, true);
    }

    /**
     * Request a search engine details
     *
     * @see https://docs.doofinder.com/api/management/v2/#operation/search_engine_show
     *
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
     * @throws \Exception
     */
    public function getSearchEngine(string $hashid): array
    {
        $url = $this->getSearchEnginePath($hashid);
        $response = $this->client->get($url);
        return json_decode($response, true);
    }

    /**
     * Creates a list of items from the index in a single bulk operation.
     *
     * @see https://docs.doofinder.com/api/management/v2/#operation/items_bulk_update
     *
     * @param array $items
     * @param string $hashId
     * @param string $indice
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     */
    public function createItemsInBulk(array $items, string $hashId, string $indice)
    {
        $path = self::ENDPOINT_UPDATE_ON_SAVE . "/{$hashId}/$indice?action=create&platform=magento2";
        $response = $this->client->post($path, $items);

        return json_decode($response, true);
    }

    /**
     * Updates a list of items from the index in a single bulk operation.
     *
     * @see https://docs.doofinder.com/api/management/v2/#operation/items_bulk_update
     *
     * @param array $items
     * @param string $hashId
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     */
    public function updateItemsInBulk(array $items, string $hashId, string $indice)
    {
        $path = self::ENDPOINT_UPDATE_ON_SAVE . "/{$hashId}/$indice?action=update&platform=magento2";
        $response = $this->client->post($path, $items);

        return json_decode($response, true);
    }

    /**
     * Deletes a list of items from the index in a single bulk operation.
     *
     * @see https://docs.doofinder.com/api/management/v2/#operation/items_bulk_delete
     *
     * @param array $items
     * @param string $hashId
     * @param string $indice
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     */
    public function deleteItemsInBulk(array $items, string $hashId, string $indice)
    {
        $path = self::ENDPOINT_UPDATE_ON_SAVE . "/{$hashId}/$indice?action=delete&platform=magento2";
        $response = $this->client->post($path, $items);

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
}
