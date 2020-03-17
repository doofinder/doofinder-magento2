<?php

namespace Doofinder\Feed\Helper;

use Zend\Serializer\Serializer as ZendSerializer;

/**
 * Helper class for serialization/deserialization
 */
class Serializer
{
    /**
     * Serialize string
     *
     * @param array|string $data
     * @return string
     */
    public function serialize($data)
    {
        return ZendSerializer::serialize($data, 'json');
    }

    /**
     * Unserialize string
     *
     * @param string $str
     * @return array
     */
    public function unserialize($str)
    {
        return ZendSerializer::unserialize($str, 'json');
    }
}
