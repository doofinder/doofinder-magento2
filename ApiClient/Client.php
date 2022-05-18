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
    const MANAGEMENT_API = 'api';

    /** Search API type */
    const SEARCH_API = 'search';

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
        string $apiKey = null,
        string $apiType = self::MANAGEMENT_API
    ) {
        $this->storeConfig  = $storeConfig;
        $this->guzzleClient = $guzzleClient;
        $this->apiType      = $apiType;
        $this->setApiTokenRegion($apiKey);
    }

    /**
     * @param string $path
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
     * @param string $path
     * @param array $body
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
    public function post(string $path, array $body): string
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
     * @param string $path
     * @param array $body
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
     * @param string $path
     * @param array $body
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
    public function delete(string $path, array $body): string
    {
        try {
            $this->response = $this->guzzleClient->delete(
                $this->getApiBaseURL() . $path,
                [
                    RequestOptions::VERIFY  => false,
                    RequestOptions::HEADERS => $this->getHeaders(),
                    RequestOptions::JSON    => $body,
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
     * @return string[]
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Token ' . $this->token,
        ];
    }

    /**
     * @param string|null $apiKey
     * @throws InvalidApiKey
     */
    private function setApiTokenRegion(?string $apiKey)
    {
        $this->apiKey = $apiKey ?? $this->storeConfig->getApiKey() ?? '';
        $clusterToken = explode('-', $this->apiKey);
        if (count($clusterToken) != 2) {
            throw new InvalidApiKey("Invalid API Key provided");
        }
        $this->clusterRegion = $clusterToken[0];
        $this->token = $clusterToken[1];
    }

    /**
     * @return string
     */
    private function getApiBaseURL(): string
    {
        return sprintf("https://%s-%s.doofinder.com", $this->clusterRegion, $this->apiType);
    }

    /**
     * @param string $message
     * @return string
     */
    private function parseErrorResponse(string $message): string
    {
        $arr = preg_split('/\r\n|\r|\n/', $message);

        return $arr[1] ?? $message;
    }
}
