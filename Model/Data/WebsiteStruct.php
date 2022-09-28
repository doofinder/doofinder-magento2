<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class WebsiteStruct implements JsonSerializable {

    private $id;
    private $name;
    private $code;
    private $installationId;
    private $storeStructs;

    public function __construct(int $id, string $code, string $name, string $installationId = null, array $storeStructs)
    {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->installationId = $installationId;
        $this->storeStructs = $storeStructs;
    }

    public function jsonSerialize()
    {
        return Array(
            'id'    => $this->id,
            'name'   => $this->name,
            'code'   => $this->code,
            'installation_id'    => $this->installationId,
            'stores'    => $this->storeStructs
         );
    }
}