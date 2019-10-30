<?php

namespace Doofinder\Feed\Model\AdditionalAttributes;

/**
 * Interface AttributesProviderInterface
 * The interface for attributes provider for Additonal Attributes validator
 */
interface AttributesProviderInterface
{
    /**
     * Get attributes codes
     * @return array
     */
    public function getAttributes();
}
