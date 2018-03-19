<?php
namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Stub,
    Throwable;

/**
 * ThrowExceptionStub
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ThrowExceptionStub implements Stub
{
    /**
     * @var  Throwable
     */
    private $expection;

    /**
     * @param  Throwable  $exception
     */
    public function __construct(Throwable $exception)
    {
        $this->expection = $exception;
    }

    /**
     * @param  Invocation  $invocation
     */
    public function invoke(Invocation $invocation)
    {
        throw $this->expection;
    }

    /**
     * @return  string
     */
    public function toString()
    {
        return 'throw exception';
    }
}
