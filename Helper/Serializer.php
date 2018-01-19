<?php

namespace Doofinder\Feed\Helper;

use \Zend\Serializer\Serializer as ZendSerializer;

/**
 * Helper class for serialization/deserialization
 */
class Serializer
{
    /**
     * Serialize string
     *
     * @param string $str
     * @return array
     */
    public function serialize($str)
    {
        return ZendSerializer::serialize($str, 'phpserialize');
    }

    /**
     * Unserialize string
     *
     * @param string $str
     * @return array
     */
    public function unserialize($str)
    {
        return ZendSerializer::unserialize($str, 'phpserialize');
    }
}
