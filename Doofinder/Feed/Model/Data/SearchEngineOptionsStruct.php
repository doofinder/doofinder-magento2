<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class SearchEngineOptionsStruct  implements JsonSerializable
{
    /**
     * @var string Store ID associated with the search engine options.
     */
    private string $storeId;

    /**
     * @var string Index URL for the search engine options.
     */
    private string $indexUrl;

    public function __construct(
        string $store_id,
        string $indexUrl
    ) {
        $this->storeId = $store_id;
        $this->indexUrl = $indexUrl;
    }

    public function getStoreId(): string
    {
        return $this->storeId;
    }

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
            "index_url" => $this->getIndexUrl()
        ];
    }
}
