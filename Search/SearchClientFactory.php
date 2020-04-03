<?php

namespace Doofinder\Feed\Search;

use Doofinder\Api\Search\ClientFactory;
use Doofinder\Api\Search\Client;

/**
 * Class SearchClientFactory
 * Factory class for API search client
 */
class SearchClientFactory
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * SearchClientFactory constructor.
     * @param ClientFactory $clientFactory
     */
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * Create client
     *
     * @param string $hashId
     * @param string $apiKey
     * @return Client
     */
    public function create($hashId, $apiKey)
    {
        return $this->clientFactory->create([
            'hashid' => $hashId,
            'api_key' => $apiKey
        ]);
    }
}
