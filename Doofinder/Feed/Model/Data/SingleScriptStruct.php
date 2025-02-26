<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class SingleScriptStruct implements JsonSerializable
{

    private $scripts;

    public function __construct(array $scripts)
    {
        $this->scripts = $scripts;
    }

    public function jsonSerialize(): array
    {
        return $this->scripts;
    }
}
