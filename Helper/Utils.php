<?php

namespace Doofinder\Feed\Helper;
use Exception;

class Utils
{
    /**
     * @param $jsonValue
     * @return mixed|string
     */
    public static function validateJSON($jsonValue)
    {
        try {
            $value = json_decode($jsonValue);

            if (is_null($value)) {
                return "";
            }
            return $value;
        } catch (\Exception $ex) {
            return "";
        }
    }
}
