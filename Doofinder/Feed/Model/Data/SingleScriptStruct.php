<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class SingleScriptStruct implements JsonSerializable
{
    /**
     * Doofinder single script per Magento website.
     *
     * @var array<string>
     */
    private $scripts;

    /**
     * ModuleStruct constructor.
     *
     * @param array<string> $scripts Doofinder single scripts.
     */
    public function __construct(array $scripts)
    {
        $this->scripts = $scripts;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->scripts;
    }
}
