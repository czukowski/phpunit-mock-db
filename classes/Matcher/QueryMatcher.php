<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    PHPUnit\Framework\Constraint\Constraint;

/**
 * QueryMatcher
 * 
 * @author   czukowski
 * @license  MIT License
 */
class QueryMatcher implements Invocation
{
    /**
     * @var  Constraint
     */
    private $constraint;

    /**
     * @param  Constraint
     */
    public function __construct(Constraint $constraint)
    {
        $this->constraint = $constraint;
    }

    /**
     * @return  string
     */
    public function toString(): string
    {
        return 'query '.$this->constraint->toString();
    }

    /**
     * @param   BaseInvocation  $invocation
     * @return  boolean
     */
    public function matches(BaseInvocation $invocation)
    {
        return $this->constraint->evaluate($invocation->getQuery(), '', TRUE);
    }

    /**
     * @param  BaseInvocation  $invocation
     */
    public function invoked(BaseInvocation $invocation)
    {}

    public function verify()
    {}
}
