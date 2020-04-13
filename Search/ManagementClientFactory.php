<?php

namespace Doofinder\Feed\Search;

use Doofinder\Api\Management\Client;
use Doofinder\Api\Management\ClientFactory;

/**
 * Class ManagementClientFactory
 * Factory class for API Management Client
 */
class ManagementClientFactory
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * ManagementClientFactory constructor.
     * @param ClientFactory $clientFactory
     */
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * Create management client
     *
     * @param string $apiKey
     * @return Client
     */
    public function create($apiKey)
    {
        return $this->clientFactory->create(['apiKey' => $apiKey]);
    }
}
