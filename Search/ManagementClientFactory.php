<?php

namespace Doofinder\Feed\Search;

/**
 * Managemenet client factory
 */
class ManagementClientFactory
{
    /**
     * Create management client
     *
     * @param string $apiKey
     * @return \Doofinder\Api\Management\Client
     */
    public function create($apiKey)
    {
        // Ignore FoundDirectInstantiation warning
        // cause this is factory
        // @codingStandardsIgnoreStart
        return new \Doofinder\Api\Management\Client($apiKey);
        // @codingStandardsIgnoreEnd
    }
}
