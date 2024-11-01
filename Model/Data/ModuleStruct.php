<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class ModuleStruct implements JsonSerializable
{

    private $version;
    private $magentoVersion;
    private $websiteStructs;

    public function __construct(string $version, string $magentoVersion, array $websiteStructs)
    {
        $this->version = $version;
        $this->magentoVersion = $magentoVersion;
        $this->websiteStructs = $websiteStructs;
    }

    public function jsonSerialize(): array
    {
        return
            ["platform" => [
                "name" => "Magento",
                "version" => $this->magentoVersion,
            ],
            "module" => [
                "options" => $this->websiteStructs,
                "version" => $this->version,
            ],
        ];
    }
}
