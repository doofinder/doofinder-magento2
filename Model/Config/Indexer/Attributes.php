<?php

namespace Doofinder\Feed\Model\Config\Indexer;

use Doofinder\Feed\Helper\StoreConfig;

/**
 * Class Attributes
 * The class responsible for providing custom Doofinder attributes used in Indexer
 */
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

    /**
     * Attributes constructor.
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
     * @return array
     */
    public function getDefaultAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param integer $storeId
     * @return array
     */
    public function get($storeId)
    {
        if (!$this->mergedAttributes) {
            $this->merge($storeId);
        }
        return $this->mergedAttributes;
    }

    /**
     * @param integer $storeId
     * @return void
     */
    private function merge($storeId)
    {
        $this->mergedAttributes = array_merge(
            $this->attributes,
            $this->storeConfig->getDoofinderFields($storeId)
        );
    }
}
