<?php
namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount as AnyInvokedCount,
    PHPUnit_Framework_MockObject_Matcher_Invocation as MockObjectMatcherInvocation,
    PHPUnit_Framework_MockObject_Matcher_InvokedCount as InvokedCount;

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
     * @param   BaseInvocation  $invocation
     * @return  boolean
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
    public function toString()
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
