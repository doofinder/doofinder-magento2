<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class SearchEngineOptionsStruct  implements JsonSerializable
{
    private string $store_id;
    private string $base_url;

    public function __construct(
        string $store_id,
        string $base_url
    ) {
        $this->store_id = $store_id;
        $this->base_url = $base_url;
    }

    public function getStoreId(): string
    {
        return $this->store_id;
    }

    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    public function getIndexUrl(): string
    {
        return $this->base_url . 'rest/' . $this->store_id . '/V1/';
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            "store_id" => $this->store_id,
            "index_url" => $this->getIndexUrl()
        ];
    }
}
