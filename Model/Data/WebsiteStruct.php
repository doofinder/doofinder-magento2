<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class WebsiteStruct implements JsonSerializable {

    private $id;
    private $name;
    private $code;
    private $storeStructs;

    public function __construct(int $id, string $name, string $code, array $storeStructs)
    {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->storeStructs = $storeStructs;
    }

    public function jsonSerialize(): array
    {
        return Array(
            'id'    => $this->id,
            'name'   => $this->name,
            'code'   => $this->code,
            'stores'    => $this->storeStructs
         );
    }
}