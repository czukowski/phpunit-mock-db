<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Stub;

/**
 * InvokeCallbackStub
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvokeCallbackStub implements Stub
{
    /**
     * @var  callable
     */
    private $callback;

    /**
     * @param  callable  $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param  Invocation  $invocation
     */
    public function invoke(Invocation $invocation): void
    {
        call_user_func($this->callback, $invocation);
    }

    /**
     * @return  string
     */
    public function toString(): string
    {
        return 'invoke callback';
    }
}
