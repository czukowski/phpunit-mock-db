<?php
namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Stub\MatcherCollection,
    PHPUnit\Framework\ExpectationFailedException;

/**
 * InvocationMocker
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvocationMocker implements MatcherCollection, Invokable
{
    /**
     * @var  array  RecordedInvocation[]
     */
    private $matchers = [];
    /**
     * @var  boolean
     */
    private $requireMatch = TRUE;

    /**
     * @param  MatcherInvocation  $matcher
     */
    public function addMatcher(MatcherInvocation $matcher)
    {
        $this->matchers[] = $matcher;
    }

    /**
     * @return  boolean
     */
    public function hasMatchers()
    {
        return count($this->matchers) > 0;
    }

    /**
     * @return  boolean
     */
    public function getRequireMatch(): bool
    {
        return $this->requireMatch;
    }

    /**
     * @param  boolean  $value
     */
    public function setRequireMatch(bool $value)
    {
        $this->requireMatch = $value;
    }

    /**
     * @param   RecordedInvocation  $matcher
     * @return  InvocationMockerBuilder
     */
    public function expects(RecordedInvocation $matcher)
    {
        return new InvocationMockerBuilder($this, $matcher);
    }

    /**
     * @param   Invocation  $invocation
     * @throws  ExpectationFailedException
     */
    public function invoke(Invocation $invocation)
    {
        $invoked = 0;
        foreach ($this->matchers as $match) {
            if ($match->matches($invocation)) {
                $match->invoked($invocation);
                $invoked++;
            }
        }
        if ( ! $invoked && $this->requireMatch) {
            throw new ExpectationFailedException(
                sprintf(
                    "No matcher found for query\n%s",
                    $invocation->getQuery()
                )
            );
        }
    }

    /**
     * @param   Invocation  $invocation
     * @return  boolean
     */
    public function matches(Invocation $invocation)
    {
        // Not sure what is this method for, other than implementing `Invokable` interface.
        // One with the same name from `PHPUnit\Framework\MockObject\InvocationMocker`
        // returns TRUE only if all matchers have matched.
        foreach ($this->matchers as $matcher) {
            if ( ! $matcher->matches($invocation)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /** 
     * @return  void
     */
    public function verify()
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }
    }
}
