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
    private const THROTTLE_RETRIES = 3;

    /**
     * Throttled object
     *
     * @var object
     */
    private $obj;

    /**
     * @param object $obj
     */
    public function __construct($obj)
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
    public function __call(string $name, ?array $args = null)
    {
        if (method_exists($this->obj, $name)) {
            return $this->throttle($name, 1, $args);
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
        usleep($seconds * 1000000);
    }

    /**
     * Throttle requests to search engine in case of ThrottledResponse error
     *
     * @param string $name Method name.
     * @param integer $counter Throttle counter.
     * @param array|null $args Method args.
     * @return mixed
     * @throws ThrottledResponse Response throttled.
     * @throws NotFound Not found.
     */
    private function throttle(string $name, int $counter = 1, ?array $args = null)
    {
        try {
            if (!is_array($args)) {
                $args = [];
            }
            return $this->obj->$name(...$args);
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

        return $this->throttle($name, $counter + 1, $args);
    }
}
