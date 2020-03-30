<?php

namespace Doofinder\Feed\Model\Attributes;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Doofinder
 * The class responsible for providing Doofinder attributes
 */
class Doofinder implements ArrayInterface
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * Doofinder constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->attributes;
    }
}
