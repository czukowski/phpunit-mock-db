<?php
namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Matcher\QueryMatcher,
    PHPUnit\Framework\ExpectationFailedException,
    RuntimeException;

/**
 * Matcher
 * 
 * @author   czukowski
 * @license  MIT License
 */
class Matcher implements MatcherInvocation
{
    /**
     * @var  RecordedInvocation
     */
    private $invocationMatcher;
    /**
     * @var  QueryMatcher
     */
    private $queryMatcher;
    /**
     * @var  Stub
     */
    private $stub;

    /**
     * @param  RecordedInvocation  $invocationMatcher
     */
    public function __construct(RecordedInvocation $invocationMatcher)
    {
        $this->invocationMatcher = $invocationMatcher;
    }

    /**
     * @return  boolean
     */
    public function hasMatchers()
    {
        return ! $this->invocationMatcher->isAnyInvokedCount();
    }

    /**
     * @return  QueryMatcher
     */
    public function getQueryMatcher()
    {
        return $this->queryMatcher;
    }

    /**
     * @return  boolean
     */
    public function hasQueryMatcher()
    {
        return $this->queryMatcher !== NULL;
    }

    /**
     * @param   QueryMatcher  $matcher
     * @throws  RuntimeException
     */
    public function setQueryMatcher(QueryMatcher $matcher)
    {
        if ($this->hasQueryMatcher()) {
            throw new RuntimeException('Query matcher is already defined, cannot redefine');
        }
        $this->queryMatcher = $matcher;
    }

    /**
     * @param  Stub  $stub
     */
    public function setStub($stub)
    {
        $this->stub = $stub;
    }

    /**
     * @param  Invocation  $invocation
     */
    public function invoked(Invocation $invocation)
    {
        $this->invocationMatcher->invoked($invocation);
        if ($this->stub) {
            $this->stub->invoke($invocation);
        }
    }

    /**
     * @param   Invocation  $invocation
     * @return  boolean
     */
    public function matches(Invocation $invocation)
    {
        if ( ! $this->invocationMatcher->matches($invocation)) {
            return FALSE;
        }
        elseif ($this->hasQueryMatcher() && ! $this->queryMatcher->matches($invocation)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * @throws  ExpectationFailedException
     */
    public function verify()
    {
        try {
            $this->invocationMatcher->verify();
            if ($this->hasQueryMatcher()
                && ! $this->invocationMatcher->isAnyInvokedCount()
                && ! $this->invocationMatcher->isNeverInvokedCount()
            ) {
                $this->queryMatcher->verify();
            }
        }
        catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException(
                sprintf(
                    "Expectation failed when %s.\n%s",
                    $this->invocationMatcher->toString(),
                    TestFailure::exceptionToString($e)
                )
            );
        }
    }

    /**
     * @return  string
     */
    public function toString(): string
    {
        $list = [];
        $list[] = $this->invocationMatcher->toString();
        if ($this->hasQueryMatcher()) {
            $list[] = 'where '.$this->queryMatcher->toString();
        }
        return implode(' ', $list);
    }
}
