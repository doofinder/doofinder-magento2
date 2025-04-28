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

    /**
     * InstallationStruct constructor.
     *
     * @param string $name Name of the installation.
     * @param string $platform Platform of the installation.
     * @param string $primaryLanguage Primary language of the installation.
     * @param bool $skipIndexation Whether to skip indexation.
     * @param string $sector Sector of the installation.
     * @param string $siteUrl Site URL of the installation.
     * @param array $searchEngines Search engines associated with the installation.
     * @param InstallationOptionsStruct $options Options for the installation.
     * @param string $queryInput Query input for the installation.
     * @param string $pluginVersion Plugin version of the installation.
     */
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

    /**
     * Get the name of the installation.
     *
     * @return string Name of the installation.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the platform of the installation.
     *
     * @return string Platform of the installation.
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * Get the primary language of the installation.
     *
     * @return string Primary language of the installation.
     */
    public function getPrimaryLanguage(): string
    {
        return $this->primaryLanguage;
    }

    /**
     * Get whether to skip indexation.
     *
     * @return bool Whether to skip indexation.
     */
    public function getSkipIndexation(): bool
    {
        return $this->skipIndexation;
    }

    /**
     * Get the sector of the installation.
     *
     * @return string Sector of the installation.
     */
    public function getSector(): string
    {
        return $this->sector;
    }

    /**
     * Get the site URL of the installation.
     *
     * @return string Site URL of the installation.
     */
    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    /**
     * Get the search engines associated with the installation.
     *
     * @return array Search engines associated with the installation.
     */
    public function getSearchEngines(): array
    {
        return $this->searchEngines;
    }

    /**
     * Get the options for the installation.
     *
     * @return InstallationOptionsStruct Options for the installation.
     */
    public function getOptions(): InstallationOptionsStruct
    {
        return $this->options;
    }

    /**
     * Get the query input for the installation.
     *
     * @return string Query input for the installation.
     */
    public function getQueryInput(): string
    {
        return $this->queryInput;
    }

    /**
     * Get the plugin version of the installation.
     *
     * @return string Plugin version of the installation.
     */
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
