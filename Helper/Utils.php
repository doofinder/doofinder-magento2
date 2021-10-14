<?php

namespace Doofinder\Feed\Helper;
use Exception;

class Utils
{
  
    public static function validateJSON($string)
    {
        try
        {
            $value  = json_decode($string);
            
            if(is_null($value))
            {
                return "";
            }
            return $value;
        }
        catch(\Exception $ex)
        {
            return "";
        }
    }
}
