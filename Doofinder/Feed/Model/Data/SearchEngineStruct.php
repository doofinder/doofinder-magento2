<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class SearchEngineStruct implements JsonSerializable
{
    private $name;
    private $language;
    private $currency;
    private $siteUrl;
    private $callbackUrl;
    private $options;

    public function __construct(
        string $name,
        string $language,
        string $currency,
        string $siteUrl,
        string $callbackUrl,
        SearchEngineOptionsStruct $options
    ) {
        $this->name = $name;
        $this->language = $language;
        $this->currency = $currency;
        $this->siteUrl = $siteUrl;
        $this->callbackUrl = $callbackUrl;
        $this->options = $options;
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
        return [
            'name' => $this->name,
            'language' => $this->language,
            'currency' => $this->currency,
            'siteUrl' => $this->siteUrl,
            'callbackUrl' => $this->callbackUrl,
            'options' => $this->options,
        ];
    }
}
