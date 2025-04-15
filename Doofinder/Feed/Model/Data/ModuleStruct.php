<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

/**
 * Data structure representing the module and Magento platform information.
 *
 * This is used to serialize version and configuration data for external usage.
 */
class ModuleStruct implements JsonSerializable
{
    /**
     * Module version.
     *
     * @var string
     */
    private $version;

    /**
     * Magento platform version.
     *
     * @var string
     */
    private $magentoVersion;

    /**
     * Array of WebsiteStructs representing configuration per website.
     *
     * @var WebsiteStruct[]
     */
    private $websiteStructs;

    /**
     * ModuleStruct constructor.
     *
     * @param string          $version         Module version.
     * @param string          $magentoVersion  Magento platform version.
     * @param WebsiteStruct[] $websiteStructs  Array of website configuration structs.
     */
    public function __construct(string $version, string $magentoVersion, array $websiteStructs)
    {
        $this->version = $version;
        $this->magentoVersion = $magentoVersion;
        $this->websiteStructs = $websiteStructs;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'platform' => [
                'name'    => 'Magento',
                'version' => $this->magentoVersion,
            ],
            'module' => [
                'options' => $this->websiteStructs,
                'version' => $this->version,
            ],
        ];
    }
}
