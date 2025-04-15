<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

class InstallationOptionsStruct implements JsonSerializable
{

    /**
     * @var int Website ID associated with the installation options.
     */
    private $websiteId;

    /**
     * @var string Token used for authentication in the installation options.
     */
    private $token;

    /**
     * InstallationOptionsStruct constructor.
     *
     * @param int $websiteId
     * @param string $token
     */
    public function __construct(int $websiteId, string $token,)
    {
        $this->websiteId = $websiteId;
        $this->token = $token;
    }

    /**
     * Get the website ID.
     *
     * @return int
     */
    public function getWebsiteId(): int
    {
        return $this->websiteId;
    }

    /**
     * Get the token.
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'website_id' => $this->getWebsiteId(),
            'token' => $this->getToken()
        ];
    }
}
