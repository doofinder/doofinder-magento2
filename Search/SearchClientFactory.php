<?php

namespace Doofinder\Feed\Search;

/**
 * Search client factory
 */
class SearchClientFactory
{
    /**
     * Create client
     *
     * @param string $hashId
     * @param string $apiKey
     * @return \Doofinder\Api\Search\Client
     */
    public function create($hashId, $apiKey)
    {
        // Ignore FoundDirectInstantiation warning
        // cause this is factory
        // @codingStandardsIgnoreStart
        return new \Doofinder\Api\Search\Client($hashId, $apiKey);
        // @codingStandardsIgnoreEnd
    }
}
