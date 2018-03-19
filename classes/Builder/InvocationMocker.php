<?php
namespace Cz\PHPUnit\MockDB\Builder;

use Cz\PHPUnit\MockDB\Matcher,
    Cz\PHPUnit\MockDB\Matcher\QueryMatcher,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Stub,
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
