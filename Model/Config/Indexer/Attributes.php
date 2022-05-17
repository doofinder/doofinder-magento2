<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Config\Indexer;

use Doofinder\Feed\Helper\StoreConfig;

class Attributes
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array|null
     */
    private $mergedAttributes;

    //TODO: Allow the user to customize the field name
    const MG_DF_ATTRIBUTES_MAP = [
        "manufacturer" => "brand"
    ];

    /**
     * Attributes constructor.
     *
     * @param StoreConfig $storeConfig
     * @param array $attributes
     */
    public function __construct(
        StoreConfig $storeConfig,
        array $attributes = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->attributes = $attributes;
    }

    /**
     * @param integer $storeId
     * @return array
     */
    public function get(int $storeId): array
    {
        if (!$this->mergedAttributes) {
            $this->merge($storeId);
        }
        return $this->mergedAttributes;
    }

    /**
     * @return array
     */
    public function getDefaultAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    private function getDoofinderAttributes(?int $storeId = null): array
    {
        return $this->storeConfig->getDoofinderAttributes($storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    private function getCustomAttributes(?int $storeId = null): array
    {
        $attributes = [];
        $raw_attributes = $this->storeConfig->getCustomAttributes($storeId);
        //Process the attributes to add only the enabled ones
        foreach ($raw_attributes as $attribute) {
            if ($attribute['enabled'] === true) {
                $attr_id = $attribute['code'];
                if (array_key_exists($attr_id, self::MG_DF_ATTRIBUTES_MAP)) {
                    $attr_id = self::MG_DF_ATTRIBUTES_MAP[$attr_id];
                }
                $attributes[$attr_id] = $attribute['code'];
            }
        }
        return $attributes;
    }

    /**
     * @param integer $storeId
     * @return void
     */
    private function merge(int $storeId)
    {
        $this->mergedAttributes = array_merge(
            $this->getDefaultAttributes(),
            $this->getDoofinderAttributes($storeId),
            $this->getCustomAttributes($storeId)
        );
    }
}
