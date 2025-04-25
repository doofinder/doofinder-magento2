<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;
use SpomkyLabs\Pki\CryptoTypes\Asymmetric\PublicKeyInfo;

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
     * @var string Callback URL for the search engine.
     */
    private $callbackUrl;

    /**
     * @var SearchEngineOptionsStruct Options for the search engine.
     */
    private $options;

    /**
     * @var string|null Doofinder store ID associated with the search engine.
     */
    private $storeId;

    /**
     * SearchEngineStruct constructor.
     *
     * @param string $name Name of the search engine.
     * @param string $language Language of the search engine.
     * @param string $currency Currency used by the search engine.
     * @param string $callbackUrl Callback URL for the search engine.
     * @param SearchEngineOptionsStruct $options Options for the search engine.
     * @param string|null $storeId Doofinder store ID associated with the search engine.
     */
    public function __construct(
        string $name,
        string $language,
        string $currency,
        string $callbackUrl,
        SearchEngineOptionsStruct $options,
        ?string $storeId = null
    ) {
        $this->name = $name;
        $this->language = $language;
        $this->currency = $currency;
        $this->callbackUrl = $callbackUrl;
        $this->options = $options;
        $this->storeId = $storeId;
    }

    /**
     * Get the name of the search engine.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the language of the search engine.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Get the currency used by the search engine.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get the callback URL for the search engine.
     *
     * @return string
     */
    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    /**
     * Get the options for the search engine.
     *
     * @return SearchEngineOptionsStruct
     */
    public function getOptions(): SearchEngineOptionsStruct
    {
        return $this->options;
    }

    /**
     * Get the Doofinder store ID associated with the search engine.
     *
     * @return string|null
     */
    public function getStoreId(): ?string
    {
        return $this->storeId;
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
            'callback_url' => $this->callbackUrl,
            'options' => $this->options,
        ];

        if ($this->storeId) {
            $data['store_id'] = $this->storeId;
        }
        return $data;
    }
}
