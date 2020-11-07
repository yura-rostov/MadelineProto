<?php

namespace danog\MadelineProto;

use Amp\Promise;

use function Amp\async;
use function Amp\await;

/**
 * Creates a promise from a generator function yielding promises.
 *
 * When a promise is yielded, execution of the generator is interrupted until the promise is resolved. A success
 * value is sent into the generator, while a failure reason is thrown into the generator. Using a coroutine,
 * asynchronous code can be written without callbacks and be structured like synchronous code.
 *
 * @deprecated Use {@see await()} and ext-fiber to await promises.
 *
 * @template-covariant TReturn
 * @template-implements Promise<TReturn>
 */
final class Coroutine implements Promise
{
    private Promise $promise;

    /**
     * @param \Generator $generator
     * @psalm-param \Generator<mixed,Promise|ReactPromise|array<array-key,
     *     Promise|ReactPromise>,mixed,Promise<TReturn>|ReactPromise|TReturn> $generator
     */
    public function __construct(\Generator $generator)
    {
        $this->promise = async(static function () use ($generator): mixed {
            $yielded = $generator->current();

            while ($generator->valid()) {
                try {
                    while (!$yielded instanceof Promise) {
                        if (!$generator->valid()) {
                            break 2;
                        }
                        if ($yielded instanceof \Generator) {
                            $yielded = new self($yielded);
                        } else if (!$yielded instanceof Promise) {
                            $yielded = $generator->send($yielded);
                        }
                    }
                    $yielded = $generator->send(await($yielded));
                } catch (\Throwable $exception) {
                    $yielded = $generator->throw($exception);
                }
            }

            return $generator->getReturn();
        });
    }

    /** @inheritDoc */
    public function onResolve(callable $onResolved): void
    {
        $this->promise->onResolve($onResolved);
    }
}
