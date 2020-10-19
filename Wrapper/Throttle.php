<?php

namespace Doofinder\Feed\Wrapper;

/**
 * Throttle wrapper
 */
class Throttle
{
    /** Max allowed throttle retries **/
    const THROTTLE_RETRIES = 3;

    /**
     * Throttled object
     *
     * @var object
     */
    private $obj;

    /**
     * @param mixed $obj
     */
    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    /**
     * Throttle every method
     *
     * @param  string $name
     * @param  array|null $args
     * @return mixed
     * @throws \BadMethodCallException Unknown method.
     */
    public function __call($name, $args)
    {
        if (method_exists($this->obj, $name)) {
            return $this->throttle($name, $args);
        }

        throw new \BadMethodCallException('Unknown method: ' . $name);
    }

    /**
     * Wait specified amount of time
     *
     * @param  integer $seconds
     * @return void
     */
    private function wait($seconds)
    {
        // phpcs:disable
        sleep($seconds);
        // phpcs:enable
    }

    /**
     * Throttle requests to search engine in case of ThrottledResponse error
     *
     * @param  string     $name    Method name.
     * @param  array|null $args    Method args.
     * @param  integer    $counter Throttle counter.
     * @return mixed
     * @throws \Doofinder\Api\Management\Errors\ThrottledResponse Response throttled.
     * @throws \Doofinder\Api\Management\Errors\NotFound Not found.
     */
    private function throttle($name, $args, $counter = 1)
    {
        try {
            // phpcs:disable
            return call_user_func_array([$this->obj, $name], $args);
            // phpcs:enable
        } catch (\Doofinder\Api\Management\Errors\ThrottledResponse $e) {
            if ($counter >= self::THROTTLE_RETRIES) {
                throw $e;
            }

            $this->wait(1);
        } catch (\Doofinder\Api\Management\Errors\IndexingInProgress $e) {
            $this->wait(3);
        } catch (\Doofinder\Api\Management\Errors\NotFound $e) {
            if ($name == 'deleteType') {
                return true;
            }
            throw $e;
        }

        return $this->throttle($name, $args, $counter + 1);
    }
}
