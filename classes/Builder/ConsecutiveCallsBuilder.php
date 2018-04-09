<?php
namespace Cz\PHPUnit\MockDB\Builder;

use Cz\PHPUnit\MockDB\Stub,
    Cz\PHPUnit\MockDB\Stub\ConsecutiveCallsStub,
    Cz\PHPUnit\MockDB\Stub\InvokeCallbackStub,
    Cz\PHPUnit\MockDB\Stub\ReturnResultSetStub,
    Cz\PHPUnit\MockDB\Stub\SetAffectedRowsStub,
    Cz\PHPUnit\MockDB\Stub\SetLastInsertIdStub,
    Cz\PHPUnit\MockDB\Stub\ThrowExceptionStub,
    Throwable;

/**
 * ConsecutiveCallsBuilder
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ConsecutiveCallsBuilder
{
    /**
     * @var  InvocationMocker
     */
    private $builder;
    /**
     * @var  ConsecutiveCallsStub
     */
    private $stub;

    /**
     * @param  InvocationMocker      $builder
     * @param  ConsecutiveCallsStub  $stub
     */
    public function __construct(InvocationMocker $builder, ConsecutiveCallsStub $stub)
    {
        $this->builder = $builder;
        $this->stub = $stub;
    }

    /**
     * @return  InvocationMocker
     */
    public function done()
    {
        return $this->builder;
    }

    /**
     * @param   Stub  $stub
     * @return  $this
     */
    public function will(Stub $stub)
    {
        $this->stub->addStub($stub);
        return $this;
    }

    /**
     * @param   callable  $callback
     * @return  $this
     */
    public function willInvokeCallback(callable $callback)
    {
        return $this->will(new InvokeCallbackStub($callback));
    }

    /**
     * @param   mixed  $resultSet
     * @return  $this
     */
    public function willReturnResultSet($resultSet)
    {
        return $this->will(new ReturnResultSetStub($resultSet));
    }

    /**
     * @param   integer  $count
     * @return  $this
     */
    public function willSetAffectedRows($count)
    {
        return $this->will(new SetAffectedRowsStub($count));
    }

    /**
     * @param   mixed  $value
     * @return  $this
     */
    public function willSetLastInsertId($value)
    {
        return $this->will(new SetLastInsertIdStub($value));
    }

    /**
     * @param   Throwable  $exception
     * @return  $this
     */
    public function willThrowException(Throwable $exception)
    {
        return $this->will(new ThrowExceptionStub($exception));
    }
}
