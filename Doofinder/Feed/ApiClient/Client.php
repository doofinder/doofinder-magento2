<?php

declare(strict_types=1);

namespace Doofinder\Feed\ApiClient;

use Doofinder\Feed\Errors\ApiClient\InvalidApiKey;
use Doofinder\Feed\Errors\BadRequest;
use Doofinder\Feed\Errors\IndexingInProgress;
use Doofinder\Feed\Errors\NotAllowed;
use Doofinder\Feed\Errors\NotFound;
use Doofinder\Feed\Errors\QuotaExhausted;
use Doofinder\Feed\Errors\ThrottledResponse;
use Doofinder\Feed\Errors\TypeAlreadyExists;
use Doofinder\Feed\Errors\Utils;
use Doofinder\Feed\Errors\WrongResponse;
use Doofinder\Feed\Helper\StoreConfig;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /** Management API type */
    public const MANAGEMENT_API = 'api';

    /** Search API type */
    public const SEARCH_API = 'search';

    /** Dooplugins type */
    public const DOOPLUGINS = 'dooplugins';

    /** @var StoreConfig */
    private $storeConfig;

    /** @var string  */
    private $apiType;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $token;

    /** @var string */
    private $clusterRegion;

    /** @var GuzzleClient  */
    private $guzzleClient;

    /** @var ResponseInterface  */
    private $response;

    /**
     * @param StoreConfig $storeConfig
     * @param GuzzleClient $guzzleClient
     * @param string $apiType
     * @param string|null $apiKey
     * @throws InvalidApiKey
     */
    public function __construct(
        StoreConfig $storeConfig,
        GuzzleClient $guzzleClient,
        string $apiType = self::MANAGEMENT_API,
        ?string $apiKey = null
    ) {
        $this->storeConfig  = $storeConfig;
        $this->guzzleClient = $guzzleClient;
        $this->apiType      = $apiType;
        $this->setApiTokenRegion($apiKey);
    }

    /**
     * Request with GET verb
     *
     * @param string $path
     * @return string
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     */
    public function get(string $path): string
    {
        try {
            $this->response = $this->guzzleClient->get(
                $this->getApiBaseURL() . $path,
                [
                    RequestOptions::VERIFY  => false,
                    RequestOptions::HEADERS => $this->getHeaders(),
                    RequestOptions::EXPECT  => '',
                ]
            );
        } catch (GuzzleException $e) {
            $errorResponse = $this->parseErrorResponse($e->getMessage());
            Utils::handleErrors($e->getCode(), $errorResponse);
            throw new BadRequest($errorResponse);
        }

        return $this->getResult();
    }

    /**
     * Request with POST verb
     *
     * @param string $path
     * @param mixed $body
     * @return string
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     */
    public function post(string $path, $body): string
    {
        try {
            $this->response = $this->guzzleClient->post(
                $this->getApiBaseURL() . $path,
                [
                    RequestOptions::VERIFY  => false,
                    RequestOptions::HEADERS => $this->getHeaders(),
                    RequestOptions::JSON    => $body,
                    RequestOptions::EXPECT  => '',
                ]
            );
        } catch (GuzzleException $e) {
            $errorResponse = $this->parseErrorResponse($e->getMessage());
            Utils::handleErrors($e->getCode(), $errorResponse);
            throw new BadRequest($errorResponse);
        }

        return $this->getResult();
    }

    /**
     * Request with PATCH verb
     *
     * @param string $path
     * @param array $body
     * @return string
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     */
    public function patch(string $path, array $body): string
    {
        try {
            $this->response = $this->guzzleClient->patch(
                $this->getApiBaseURL() . $path,
                [
                    RequestOptions::VERIFY  => false,
                    RequestOptions::HEADERS => $this->getHeaders(),
                    RequestOptions::JSON    => $body,
                    RequestOptions::EXPECT  => '',
                ]
            );
        } catch (GuzzleException $e) {
            $errorResponse = $this->parseErrorResponse($e->getMessage());
            Utils::handleErrors($e->getCode(), $errorResponse);
            throw new BadRequest($errorResponse);
        }

        return $this->getResult();
    }

    /**
     * Request with DELETE verb
     *
     * @param string $path
     * @param array $body
     * @return string
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     */
    public function delete(string $path, array $body): string
    {
        try {
            $this->response = $this->guzzleClient->delete(
                $this->getApiBaseURL() . $path,
                [
                    RequestOptions::VERIFY  => false,
                    RequestOptions::HEADERS => $this->getHeaders(),
                    RequestOptions::JSON    => $this->flattenArray($body),
                    RequestOptions::EXPECT  => '',
                ]
            );
        } catch (GuzzleException $e) {
            $errorResponse = $e->getResponse()->getBody()->getContents();
            Utils::handleErrors($e->getCode(), $errorResponse);
            throw new BadRequest($errorResponse);
        }

        return $this->getResult();
    }

    /**
     * Gets results handling errors
     *
     * @return string
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     */
    private function getResult(): string
    {
        if (!$this->response) {
            throw new TransferException();
        }
        $statusCode      = (int)$this->response->getStatusCode();
        $contentResponse = $this->response->getBody()->getContents();
        Utils::handleErrors($statusCode, $contentResponse);

        return $contentResponse;
    }

    /**
     * Gets necessary headers for the requests
     *
     * @return string[]
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Token ' . $this->token,
        ];
    }

    /**
     * Sets API token region in order to match the corresponding one
     *
     * @param string|null $apiKey
     * @throws InvalidApiKey
     */
    private function setApiTokenRegion(?string $apiKey)
    {
        $this->apiKey = $apiKey ?? $this->storeConfig->getApiKey() ?? '';
        $clusterToken = explode('-', str_replace('https://', '', $this->apiKey));
        if (count($clusterToken) != 2) {
            throw new InvalidApiKey("Invalid API Key provided");
        }
        $this->clusterRegion = $clusterToken[0];
        $this->token = $clusterToken[1];
    }

    /**
     * Gets the base URL to make request to Doofinder depending on the service.
     *
     * @return string
     */
    private function getApiBaseURL(): string
    {
        $url = getenv("DOOFINDER_ADMIN_URL") ?: "https://admin.doofinder.com";
        switch ($this->apiType) {
            case self::DOOPLUGINS:
                $url = sprintf(getenv("DOOFINDER_PLUGINS_URL_FORMAT") ?:
                    "https://%s-plugins.doofinder.com", $this->clusterRegion);
                break;
            case self::SEARCH_API:
                $url = sprintf(getenv("DOOFINDER_SEARCH_URL_FORMAT") ?:
                    "https://%s-search.doofinder.com", $this->clusterRegion);
                break;
            case self::MANAGEMENT_API:
                $url = sprintf(getenv("DOOFINDER_API_URL_FORMAT") ?:
                    "https://%s-api.doofinder.com", $this->clusterRegion);
                break;
        }
        return $url;
    }

    /**
     * Parses error response taking into account line breaks
     *
     * @param string $message
     * @return string
     */
    private function parseErrorResponse(string $message): string
    {
        $arr = preg_split('/\r\n|\r|\n/', $message);

        return $arr[1] ?? $message;
    }

    /**
     * Flattens a multidimensional array of IDs into a single-level array.
     *
     * This utility method takes an array that may contain nested arrays of IDs
     * and flattens it into a single, one-dimensional array.
     *
     * Example:
     * Input: [1, [2, 3], [[4], 5]]
     * Output: [1, 2, 3, 4, 5]
     *
     * @param mixed[] $ids A multidimensional array potentially containing nested arrays of IDs.
     * @return int[] A flat array containing all IDs from the input array.
     */
    private function flattenArray(array $ids): array
    {
        $flattened_ids = [];
        array_walk_recursive($ids, function ($id) use (&$flattened_ids) {
            $flattened_ids[] = $id;
        });
        return $flattened_ids;
    }
}
