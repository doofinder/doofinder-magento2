<?php

namespace Doofinder\Feed\Wrapper;

class Throttle
{
    /** Max allowed throttle retries **/
    const THROTTLE_RETRIES = 3;

    /** Max allowed indexing retries **/
    const INDEXING_RETRIES = 10;

    /**
     * Throttled object
     *
     * @var Object
     */
    private $_obj;

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
     * Wait specified amount of time
     *
     * @param int $seconds
     */
    private function wait($seconds)
    {
        // @codingStandardsIgnoreStart
        sleep($seconds);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Throttle requests to search engine in case of ThrottledResponse error
     *
     * @param string $name Method name
     * @param array $args Method args
     * @param int $counter = 1 Throttle counter
     */
    private function throttle($name, $args, $counter = 1)
    {
        try {
            // @codingStandardsIgnoreStart
            return call_user_func_array([$this->_obj, $name], $args);
            // @codingStandardsIgnoreEnd
        } catch (\Doofinder\Api\Management\Errors\ThrottledResponse $e) {
            if ($counter >= self::THROTTLE_RETRIES) {
                throw $e;
            }

            $this->wait(1);
        } catch (\Doofinder\Api\Management\Errors\IndexingInProgress $e) {
            if ($counter >= self::INDEXING_RETRIES) {
                throw $e;
            }

            $this->wait(3);
        }

        return $this->throttle($name, $args, $counter + 1);
    }
}
