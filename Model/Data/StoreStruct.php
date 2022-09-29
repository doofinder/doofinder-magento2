<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class StoreStruct implements JsonSerializable {

    private $id;
    private $code;
    private $language;
    private $currency;

    public function __construct(int $id, string $code, string $language, string $currency)
    {
        $this->id = $id;
        $this->code = $code;
        $this->language = $language;
        $this->currency = $currency;
    }

    public function jsonSerialize()
    {
        return Array(
            'id'    => $this->id,
            'code'   => $this->code,
            'language'    => $this->language,
            'currency'    => $this->currency
         );
    }
}