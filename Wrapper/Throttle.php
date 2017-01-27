<?php

namespace Doofinder\Feed\Wrapper;

class Throttle
{
    /** Max allowed throttle retries **/
    const THROTTLE_RETRIES = 3;

    /**
     * Throttled object
     *
     * @var Object
     */
    protected $_obj;

    /**
     * @param Object $obj
     */
    public function __construct($obj)
    {
        $this->_obj = $obj;
    }

    public function __call($name, $args)
    {
        if (method_exists($this->_obj, $name)) {
            return $this->throttle($name, $args);
        }

        throw new \BadMethodCallException('Unknown method: ' . $name);
    }

    /**
     * Throttle requests to search engine in case of ThrottledResponse error
     *
     * @param string $name Method name
     * @param array $args Method args
     * @param int $counter = 1 Throttle counter
     */
    protected function throttle($name, $args, $counter = 1)
    {
        try {
            return call_user_func_array(array($this->_obj, $name), $args);
        } catch (\Doofinder\Api\Management\Errors\ThrottledResponse $e) {
            if ($counter >= self::THROTTLE_RETRIES) {
                throw $e;
            }

            sleep(1);
            return $this->throttle($name, $args, $counter + 1);
        }
    }
}
