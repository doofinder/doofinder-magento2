<?php

namespace Doofinder\Feed\Model\Data;

use JsonSerializable;

/**
 * Data structure representing a Magento website with its associated stores.
 *
 * Implements JsonSerializable to allow easy JSON encoding of website data.
 */
class WebsiteStruct implements JsonSerializable
{
    /**
     * Website ID.
     *
     * @var int
     */
    private $id;

    /**
     * Website name.
     *
     * @var string
     */
    private $name;

    /**
     * Website code.
     *
     * @var string
     */
    private $code;

    /**
     * Array of store data structures associated with the website.
     *
     * @var StoreStruct[]
     */
    private $storeStructs;

    /**
     * WebsiteStruct constructor.
     *
     * @param int           $id            Website ID.
     * @param string        $name          Website name.
     * @param string        $code          Website code.
     * @param StoreStruct[] $storeStructs  Array of StoreStruct instances.
     */
    public function __construct(int $id, string $name, string $code, array $storeStructs)
    {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->storeStructs = $storeStructs;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'code'   => $this->code,
            'stores' => $this->storeStructs,
        ];
    }
}
