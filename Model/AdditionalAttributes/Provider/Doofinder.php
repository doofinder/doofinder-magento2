<?php

namespace Doofinder\Feed\Model\AdditionalAttributes\Provider;

use Doofinder\Feed\Model\AdditionalAttributes\AttributesProviderInterface;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * Class Doofinder
 */
class Doofinder implements AttributesProviderInterface
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
     * Doofinder constructor.
     * @param StoreConfig $storeConfig
     */
    public function __construct(StoreConfig $storeConfig)
    {
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
                $this->storeConfig->getDefaultDoofinderFields()
            );
        }
        return $this->attributes;
    }
}
