<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    PHPUnit\Framework\MockObject\Rule\AnyInvokedCount,
    PHPUnit\Framework\MockObject\Rule\InvokedCount,
    PHPUnit\Framework\MockObject\Rule\InvocationOrder;

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
     * @var  InvocationOrder
     */
    private $invocation;

    /**
     * @param  InvocationOrder       $invocation
     * @param  InvocationsContainer  $container
     */
    public function __construct(InvocationOrder $invocation, InvocationsContainer $container)
    {
        $this->container = $container;
        $this->invocation = $invocation;
    }

    /**
     * @param  BaseInvocation  $invocation
     */
    public function invoked(BaseInvocation $invocation): void
    {
        $this->invocation->invoked($this->container->getMockObjectInvocation($invocation));
    }

    /**
     * @param   BaseInvocation  $invocation
     * @return  boolean
     */
    public function matches(BaseInvocation $invocation): bool
    {
        return $this->invocation->matches($this->container->getMockObjectInvocation($invocation));
    }

    public function verify(): void
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
    public function isAnyInvokedCount(): bool
    {
        return $this->invocation instanceof AnyInvokedCount;
    }

    /**
     * @return  boolean
     */
    public function isNeverInvokedCount(): bool
    {
        return $this->invocation instanceof InvokedCount && $this->invocation->isNever();
    }
}
