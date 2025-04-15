<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

/**
 * Data structure representing a Magento store.
 *
 * Used to encapsulate basic store information for JSON serialization.
 */
class StoreStruct implements JsonSerializable
{
    /**
     * Store ID.
     *
     * @var int
     */
    private $id;

    /**
     * Store code.
     *
     * @var string
     */
    private $code;

    /**
     * Store language (locale code).
     *
     * @var string
     */
    private $language;

    /**
     * Store currency code.
     *
     * @var string
     */
    private $currency;

    /**
     * Doofinder installation ID for the store.
     *
     * @var string
     */
    private $installationId;

    /**
     * StoreStruct constructor.
     *
     * @param int    $id              Store ID.
     * @param string $code            Store code.
     * @param string $language        Language/locale code.
     * @param string $currency        Currency code.
     * @param string $installationId  Doofinder installation ID.
     */
    public function __construct(int $id, string $code, string $language, string $currency, string $installationId)
    {
        $this->id = $id;
        $this->code = $code;
        $this->language = $language;
        $this->currency = $currency;
        $this->installationId = $installationId;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id'              => $this->id,
            'code'            => $this->code,
            'language'        => $this->language,
            'currency'        => $this->currency,
            'installationId'  => $this->installationId,
        ];
    }
}
