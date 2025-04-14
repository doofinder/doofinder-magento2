<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class SearchEngineStruct implements JsonSerializable
{
    /**
     * @var string Name of the search engine.
     */
    private $name;

    /**
     * @var string Language of the search engine.
     */
    private $language;

    /**
     * @var string Currency used by the search engine.
     */
    private $currency;

    /**
     * @var string URL of the site associated with the search engine.
     */
    private $siteUrl;

    /**
     * @var string Callback URL for the search engine.
     */
    private $callbackUrl;

    /**
     * @var SearchEngineOptionsStruct Options for the search engine.
     */
    private $options;

    /**
     * @var string|null Store ID associated with the search engine.
     */
    private $storeId;

    public function __construct(
        string $name,
        string $language,
        string $currency,
        string $siteUrl,
        string $callbackUrl,
        SearchEngineOptionsStruct $options,
        ?string $storeId = null
    ) {
        $this->name = $name;
        $this->language = $language;
        $this->currency = $currency;
        $this->siteUrl = $siteUrl;
        $this->callbackUrl = $callbackUrl;
        $this->options = $options;
        $this->storeId = $storeId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function getOptions(): SearchEngineOptionsStruct
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = [
            'name' => $this->name,
            'language' => $this->language,
            'currency' => $this->currency,
            'site_url' => $this->siteUrl,
            'callback_url' => $this->callbackUrl,
            'options' => $this->options,
        ];

        if ($this->storeId) {
            $data['store_id'] = $this->storeId;
        }
        return $data;
    }
}
