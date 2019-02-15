<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount,
    PHPUnit\Framework\MockObject\Matcher\Invocation as MockObjectMatcherInvocation,
    PHPUnit\Framework\MockObject\Matcher\InvokedCount;

/**
 * MatcherInvocationWrapper
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MatcherInvocationWrapper implements RecordedInvocation
{
    /**
     * @var  InvocationsContainer
     */
    private $container;
    /**
     * @var  MockObjectMatcherInvocation
     */
    private $invocation;

    /**
     * @param  MockObjectMatcherInvocation  $invocation
     * @param  InvocationsContainer         $container
     */
    public function __construct(MockObjectMatcherInvocation $invocation, InvocationsContainer $container)
    {
        $this->container = $container;
        $this->invocation = $invocation;
    }

    /**
     * @param  BaseInvocation  $invocation
     */
    public function invoked(BaseInvocation $invocation)
    {
        $this->invocation->invoked($this->container->getMockObjectInvocation($invocation));
    }

    /**
     * @param  BaseInvocation  $invocation
     */
    public function matches(BaseInvocation $invocation)
    {
        return $this->invocation->matches($this->container->getMockObjectInvocation($invocation));
    }

    public function verify()
    {
        $this->invocation->verify();
    }

    /**
     * @return  string
     */
    public function toString(): string
    {
        return $this->invocation->toString();
    }

    /**
     * @return  boolean
     */
    public function isAnyInvokedCount()
    {
        return $this->invocation instanceof AnyInvokedCount;
    }

    /**
     * @return  boolean
     */
    public function isNeverInvokedCount()
    {
        return $this->invocation instanceof InvokedCount && $this->invocation->isNever();
    }
}
