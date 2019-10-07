<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Invocation\QueryInvocation,
    Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\MockObject\InvocationsContainer,
    Cz\PHPUnit\MockDB\MockObject\MatcherInvocationWrapper,
    PHPUnit\Framework\MockObject\Rule\InvocationOrder,
    PHPUnit\Framework\InvalidArgumentException;

/**
 * Mock
 * 
 * @author   czukowski
 * @license  MIT License
 */
class Mock
{
    /**
     * @var  InvocationsContainer
     */
    private $invocationsContainer;
    /**
     * @var  InvocationMocker
     */
    private $invocationMocker;

    /**
     * @param   MatcherInvocation|InvocationOrder  $matcher
     * @return  InvocationMockerBuilder
     * @throws  InvalidArgumentException
     */
    public function expects($matcher)
    {
        if ($matcher instanceof InvocationOrder) {
            $matcher = new MatcherInvocationWrapper($matcher, $this->getInvocationsContainer());
        }
        if ( ! $matcher instanceof MatcherInvocation) {
            throw InvalidArgumentException::create(
                1,
                sprintf('object implementing interface %s\Matcher\Invocation', __NAMESPACE__)
            );
        }
        return $this->getInvocationMocker()
            ->expects($matcher);
    }

    /**
     * @param   Invocation|string  $query
     * @return  Invocation
     */
    public function invoke($query)
    {
        $invocation = $query instanceof Invocation ? $query : new QueryInvocation($query);
        $this->getInvocationMocker()
            ->invoke($invocation);
        return $invocation;
    }

    /**
     * @return  void
     */
    public function verify(): void
    {
        $this->getInvocationMocker()
            ->verify();
    }

    /**
     * @return  InvocationMocker
     */
    public function getInvocationMocker(): InvocationMocker
    {
        if ($this->invocationMocker === NULL) {
            $this->invocationMocker = new InvocationMocker;
        }
        return $this->invocationMocker;
    }

    /**
     * @return  void
     */
    public function unsetInvocationMocker(): void
    {
        $this->invocationMocker = NULL;
    }

    /**
     * @return  InvocationsContainer
     */
    protected function getInvocationsContainer(): InvocationsContainer
    {
        if ($this->invocationsContainer === NULL) {
            $this->invocationsContainer = new InvocationsContainer;
        }
        return $this->invocationsContainer;
    }

    /**
     * @return  boolean
     */
    public function getRequireMatch(): bool
    {
        return $this->getInvocationMocker()
            ->getRequireMatch();
    }

    /**
     * @param   boolean  $value
     * @return  $this
     */
    public function setRequireMatch(bool $value): self
    {
        $this->getInvocationMocker()
            ->setRequireMatch($value);
        return $this;
    }
}
