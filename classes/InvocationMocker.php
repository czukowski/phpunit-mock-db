<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Stub\MatcherCollection,
    PHPUnit\Framework\ExpectationFailedException,
    SebastianBergmann\Exporter\Exporter;

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
    public function addMatcher(MatcherInvocation $matcher): void
    {
        $this->matchers[] = $matcher;
    }

    /**
     * @return  boolean
     */
    public function hasMatchers(): bool
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
    public function setRequireMatch(bool $value): void
    {
        $this->requireMatch = $value;
    }

    /**
     * @param   RecordedInvocation  $matcher
     * @return  InvocationMockerBuilder
     */
    public function expects(RecordedInvocation $matcher): InvocationMockerBuilder
    {
        return new InvocationMockerBuilder($this, $matcher);
    }

    /**
     * @param   Invocation  $invocation
     * @throws  ExpectationFailedException
     */
    public function invoke(Invocation $invocation): void
    {
        $invoked = 0;
        foreach ($this->matchers as $match) {
            if ($match->matches($invocation)) {
                $match->invoked($invocation);
                $invoked++;
            }
        }
        if ( ! $invoked && $this->requireMatch) {
            $parameters = $invocation->getParameters();
            $exporter = new Exporter;

            throw new ExpectationFailedException(
                sprintf(
                    "No matcher found for query\n%s%s",
                    $invocation->getQuery(),
                    $parameters !== []
                        ? sprintf("\nwith parameters: [%s]", $exporter->shortenedRecursiveExport($parameters))
                        : ''
                )
            );
        }
    }

    /**
     * @param   Invocation  $invocation
     * @return  boolean
     */
    public function matches(Invocation $invocation): bool
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
    public function verify(): void
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }
    }
}
