<?php
declare(strict_types=1);

namespace Doofinder\Feed\Wrapper;

use Doofinder\Feed\Errors\IndexingInProgress;
use Doofinder\Feed\Errors\NotFound;
use Doofinder\Feed\Errors\ThrottledResponse;

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
     * @param object $obj
     */
    public function __construct(object $obj)
    {
        $this->obj = $obj;
    }

    /**
     * Throttle every method
     *
     * @param string $name
     * @param array|null $args
     * @return mixed
     * @throws NotFound
     * @throws ThrottledResponse
     */
    public function __call(string $name, array $args = null)
    {
        if (method_exists($this->obj, $name)) {
            return $this->throttle($name, $args);
        }

        throw new \BadMethodCallException('Unknown method: ' . $name);
    }

    /**
     * Wait specified amount of time
     *
     * @param integer $seconds
     * @return void
     */
    private function wait(int $seconds)
    {
        // phpcs:disable
        sleep($seconds);
        // phpcs:enable
    }

    /**
     * Throttle requests to search engine in case of ThrottledResponse error
     *
     * @param string $name Method name.
     * @param array|null $args Method args.
     * @param integer $counter Throttle counter.
     * @return mixed
     * @throws ThrottledResponse Response throttled.
     * @throws NotFound Not found.
     */
    private function throttle(string $name, array $args = null, int $counter = 1)
    {
        try {
            // phpcs:disable
            return call_user_func_array([$this->obj, $name], $args);
            // phpcs:enable
        } catch (ThrottledResponse $e) {
            if ($counter >= self::THROTTLE_RETRIES) {
                throw $e;
            }
            $this->wait(1);
        } catch (IndexingInProgress $e) {
            $this->wait(3);
        } catch (NotFound $e) {
            if ($name == 'deleteType') {
                return true;
            }
            throw $e;
        }

        return $this->throttle($name, $args, $counter + 1);
    }
}
