<?php

namespace Doofinder\Feed\Model\AdditionalAttributes\Provider;

use Doofinder\Feed\Model\AdditionalAttributes\AttributesProviderInterface;
use Doofinder\Feed\Model\Config\Indexer\Attributes;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * Class Doofinder
 */
class Doofinder implements AttributesProviderInterface
{
    /**
     * @var Attributes
     */
    private $indexerAttributes;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var array
     */
    private $attributes;

    /**
     * Doofinder constructor.
     * @param Attributes $indexerAttributes
     * @param StoreConfig $storeConfig
     */
    public function __construct(
        Attributes $indexerAttributes,
        StoreConfig $storeConfig
    ) {
        $this->indexerAttributes = $indexerAttributes;
        $this->storeConfig = $storeConfig;
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->attributes) {
            $this->attributes = array_keys(
                $this->indexerAttributes->get(
                    $this->storeConfig->getCurrentStore()->getId()
                )
            );
        }
        return $this->attributes;
    }
}
