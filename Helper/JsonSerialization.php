<?php

declare(strict_types=1);

namespace Doofinder\Feed\Helper;

class JsonSerialization
{

    /**
     * Encodes PHP data structure to a JSON string using available JSON encoding classes
     *
     * @param mixed $data The PHP data structure to encode into JSON format
     *
     * @return string The JSON string representation of the input data
     */
    public static function encode($data)
    {
        json_encode($data);
    }
}
