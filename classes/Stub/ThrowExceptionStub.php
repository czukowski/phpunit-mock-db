<?php
namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Stub,
    Exception;

/**
 * ThrowExceptionStub
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ThrowExceptionStub implements Stub
{
    /**
     * @var  Exception
     */
    private $exception;

    /**
     * @param  Exception  $exception
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @param  Invocation  $invocation
     */
    public function invoke(Invocation $invocation)
    {
        throw $this->exception;
    }

    /**
     * @return  string
     */
    public function toString()
    {
        return 'throw exception';
    }
}
