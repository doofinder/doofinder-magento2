<?php

namespace Doofinder\Feed\Helper;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Helper class for serialization/deserialization
 */
class Serializer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Serializer constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serialize string
     *
     * @param array|string $data
     * @return string
     */
    public function serialize($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Unserialize string
     *
     * @param string $str
     * @return array
     */
    public function unserialize($str)
    {
        return $this->serializer->unserialize($str);
    }
}
