<?php

namespace Amp\Internal;

use Interop\Async\Loop;

/**
 * Stores a set of functions to be invoked when an awaitable is resolved.
 *
 * @internal
 */
class WhenQueue {
    /**
     * @var callable[]
     */
    private $queue = [];

    /**
     * @param callable|null $callback Initial callback to add to queue.
     */
    public function __construct(callable $callback = null) {
        if (null !== $callback) {
            $this->push($callback);
        }
    }

    /**
     * Calls each callback in the queue, passing the provided values to the function.
     *
     * @param \Throwable|\Exception|null $exception
     * @param mixed $value
     */
    public function __invoke($exception = null, $value = null) {
        foreach ($this->queue as $callback) {
            try {
                $callback($exception, $value);
            } catch (\Throwable $exception) {
                Loop::defer(static function () use ($exception) {
                    throw $exception;
                });
            } catch (\Exception $exception) {
                Loop::defer(static function () use ($exception) {
                    throw $exception;
                });
            }
        }
    }

    /**
     * Unrolls instances of self to avoid blowing up the call stack on resolution.
     *
     * @param callable $callback
     */
    public function push(callable $callback) {
        if ($callback instanceof self) {
            $this->queue = \array_merge($this->queue, $callback->queue);
            return;
        }

        $this->queue[] = $callback;
    }
}
