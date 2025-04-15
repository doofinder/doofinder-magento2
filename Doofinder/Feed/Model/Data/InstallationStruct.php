<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class InstallationStruct implements JsonSerializable
{
    /**
     * @var string Name of the installation.
     */
    private $name;

    /**
     * @var string Platform of the installation.
     */
    private $platform;

    /**
     * @var string Primary language of the installation.
     */
    private $primaryLanguage;

    /**
     * @var bool Whether to skip indexation.
     */
    private $skipIndexation;

    /**
     * @var string Sector of the installation.
     */
    private $sector;

    /**
     * @var string Site URL of the installation.
     */
    private $siteUrl;

    /**
     * @var array Search engines associated with the installation.
     */
    private $searchEngines;

    /**
     * @var InstallationOptionsStruct Options for the installation.
     */
    private $options;

    /**
     * @var string Query input for the installation.
     */
    private $queryInput;

    /**
     * @var string Plugin version of the installation.
     */
    private $pluginVersion;

    public function __construct(
        string $name,
        string $platform,
        string $primaryLanguage,
        bool $skipIndexation,
        string $sector,
        string $siteUrl,
        array $searchEngines,
        InstallationOptionsStruct $options,
        string $queryInput,
        string $pluginVersion
    ) {
        $this->name = $name;
        $this->platform = $platform;
        $this->primaryLanguage = $primaryLanguage;
        $this->skipIndexation = $skipIndexation;
        $this->sector = $sector;
        $this->siteUrl = $siteUrl;
        $this->searchEngines = $searchEngines;
        $this->options = $options;
        $this->queryInput = $queryInput;
        $this->pluginVersion = $pluginVersion;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getPrimaryLanguage(): string
    {
        return $this->primaryLanguage;
    }

    public function getSkipIndexation(): bool
    {
        return $this->skipIndexation;
    }

    public function getSector(): string
    {
        return $this->sector;
    }

    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    public function getSearchEngines(): array
    {
        return $this->searchEngines;
    }

    public function getOptions(): InstallationOptionsStruct
    {
        return $this->options;
    }

    public function getQueryInput(): string
    {
        return $this->queryInput;
    }

    public function getPluginVersion(): string
    {
        return $this->pluginVersion;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'platform' => $this->platform,
            'primary_language' => $this->primaryLanguage,
            'skip_indexation' => $this->skipIndexation,
            'sector' => $this->sector,
            'site_url' => $this->siteUrl,
            'search_engines' => $this->searchEngines,
            'options' => $this->options,
            'query_input' => $this->queryInput,
            'plugin_version' => $this->pluginVersion,
        ];
    }
}
