<?php

namespace Doofinder\Feed\Model\AdditionalAttributes\Provider;

use Doofinder\Feed\Model\AdditionalAttributes\AttributesProviderInterface;
use Doofinder\Feed\Model\Config\Indexer\Attributes;

/**
 * Class Doofinder
 * The class responsible for providing Doofinder attributes code
 */
class Doofinder implements AttributesProviderInterface
{
    /**
     * @var Attributes
     */
    private $indexerAttributes;

    /**
     * @var array
     */
    private $attributes;

    /**
     * Doofinder constructor.
     * @param Attributes $indexerAttributes
     */
    public function __construct(Attributes $indexerAttributes)
    {
        $this->indexerAttributes = $indexerAttributes;
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->attributes) {
            $this->attributes = array_keys(
                $this->indexerAttributes->getDefaultAttributes()
            );
        }
        return $this->attributes;
    }
}
