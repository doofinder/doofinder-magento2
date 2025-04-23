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
     * @var int Store Group ID associated with the installation options.
     */
    private $storeGroupId;

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
    public function __construct(int $websiteId, int $storeGroupId, string $token)
    {
        $this->websiteId = $websiteId;
        $this->storeGroupId = $storeGroupId;
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
     * Get the store group ID.
     *
     * @return int
     */
    public function getStoreGroupId(): int
    {
        return $this->storeGroupId;
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
            'store_group_id' => $this->getStoreGroupId(),
            'token' => $this->getToken()
        ];
    }
}
