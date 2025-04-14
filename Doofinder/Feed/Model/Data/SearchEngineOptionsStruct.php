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
     * @var string Base URL for the search engine options.
     */
    private string $base_url;

    public function __construct(
        string $store_id,
        string $base_url
    ) {
        $this->storeId = $store_id;
        $this->base_url = $base_url;
    }

    public function getStoreId(): string
    {
        return $this->storeId;
    }

    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    public function getIndexUrl(): string
    {
        return $this->base_url . 'rest/' . $this->storeId . '/V1/';
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
