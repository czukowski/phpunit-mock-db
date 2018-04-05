<?php
namespace Cz\PHPUnit\MockDB\Builder;

use Cz\PHPUnit\MockDB\Matcher,
    Cz\PHPUnit\MockDB\Matcher\QueryMatcher,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Stub,
    Cz\PHPUnit\MockDB\Stub\ConsecutiveCallsStub,
    Cz\PHPUnit\MockDB\Stub\MatcherCollection,
    Cz\PHPUnit\MockDB\Stub\ReturnResultSetStub,
    Cz\PHPUnit\MockDB\Stub\SetAffectedRowsStub,
    Cz\PHPUnit\MockDB\Stub\SetLastInsertIdStub,
    Cz\PHPUnit\MockDB\Stub\ThrowExceptionStub,
    Cz\PHPUnit\SQL\EqualsSQLQueriesConstraint,
    PHPUnit\Framework\Constraint\Constraint,
    Throwable;

/**
 * InvocationMocker
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvocationMocker
{
    /**
     * @var  MatcherCollection
     */
    private $collection;
    /**
     * @var  Matcher
     */
    private $matcher;

    /**
     * @param  MatcherCollection   $collection
     * @param  RecordedInvocation  $invocationMatcher
     */
    public function __construct(MatcherCollection $collection, RecordedInvocation $invocationMatcher)
    {
        $this->collection = $collection;
        $this->matcher = new Matcher($invocationMatcher);
        $this->collection->addMatcher($this->matcher);
    }

    /**
     * @param   Constraint|string  $constraint
     * @return  $this
     */
    public function query($constraint)
    {
        if (is_string($constraint)) {
            $constraint = new EqualsSQLQueriesConstraint($constraint);
        }
        $this->matcher->setQueryMatcher(new QueryMatcher($constraint));
        return $this;
    }

    /**
     * @param   Stub  $stub
     * @return  $this
     */
    public function will(Stub $stub)
    {
        $this->matcher->setStub($stub);
        return $this;
    }

    /**
     * @param   mixed  $resultSet
     * @param   mixed  $nextSets ...
     * @return  $this
     */
    public function willReturnResultSet($resultSet, ...$nextSets)
    {
        return $this->createStub(
            function ($argument) {
                return new ReturnResultSetStub($argument);
            },
            $resultSet,
            $nextSets
        );
    }

    /**
     * @param   integer  $count
     * @param   integer  $nextCounts ...
     * @return  $this
     */
    public function willSetAffectedRows($count, ...$nextCounts)
    {
        return $this->createStub(
            function ($argument) {
                return new SetAffectedRowsStub($argument);
            },
            $count,
            $nextCounts
        );
    }

    /**
     * @param   mixed  $value
     * @param   mixed  $nextValues ...
     * @return  $this
     */
    public function willSetLastInsertId($value, ...$nextValues)
    {
        return $this->createStub(
            function ($argument) {
                return new SetLastInsertIdStub($argument);
            },
            $value,
            $nextValues
        );
    }

    /**
     * @param   Throwable  $exception
     * @param   Throwable  $nextExceptions ...
     * @return  $this
     */
    public function willThrowException(Throwable $exception, ...$nextExceptions)
    {
        return $this->createStub(
            function ($argument) {
                return new ThrowExceptionStub($argument);
            },
            $exception,
            $nextExceptions
        );
    }

    /**
     * @param   callable  $callback
     * @param   mixed     $argument
     * @param   array     $nextArguments
     * @return  $this
     */
    private function createStub(callable $callback, $argument, array $nextArguments)
    {
        if ( ! $nextArguments) {
            return $this->will($callback($argument));
        }
        return $this->will(
            new ConsecutiveCallsStub(
                array_map(
                    $callback,
                    array_merge([$argument], $nextArguments)
                )
            )
        );
    }
}
