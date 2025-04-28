<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class SearchEngineOptionsStruct implements JsonSerializable
{
    /**
     * @var string Store ID associated with the search engine options.
     */
    private string $storeId;

    /**
     * @var string Index URL for the search engine options.
     */
    private string $indexUrl;

    /**
     * SearchEngineOptionsStruct constructor.
     *
     * @param string $storeId Store ID associated with the search engine options.
     * @param string $indexUrl Index URL for the search engine options.
     */
    public function __construct(
        string $storeId,
        string $indexUrl
    ) {
        $this->storeId = $storeId;
        $this->indexUrl = $indexUrl;
    }

    /**
     * Get the store ID.
     *
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * Get the index URL.
     *
     * @return string
     */
    public function getIndexUrl(): string
    {
        return $this->indexUrl;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            "store_id" => $this->storeId,
            "index_url" => $this->indexUrl
        ];
    }
}
