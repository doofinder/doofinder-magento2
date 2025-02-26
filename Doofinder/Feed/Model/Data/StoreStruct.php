<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class StoreStruct implements JsonSerializable
{

    private $id;
    private $code;
    private $language;
    private $currency;
    private $installationId;

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
            'id'    => $this->id,
            'code'   => $this->code,
            'language'    => $this->language,
            'currency'    => $this->currency,
            'installationId'    => $this->installationId
        ];
    }
}
