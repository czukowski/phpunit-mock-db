<?php
namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Invocation\QueryInvocation,
    Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\MockObject\InvocationsContainer,
    Cz\PHPUnit\MockDB\MockObject\MatcherInvocationWrapper,
    PHPUnit_Framework_Exception as Exception,
    PHPUnit_Framework_MockObject_Matcher_Invocation as MockObjectMatcherInvocation,
    PHPUnit_Util_InvalidArgumentHelper as InvalidArgumentHelper;

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
     * @param   MatcherInvocation|MockObjectMatcherInvocation  $matcher
     * @return  InvocationMockerBuilder
     * @throws  Exception
     */
    public function expects($matcher)
    {
        if ($matcher instanceof MockObjectMatcherInvocation) {
            $matcher = new MatcherInvocationWrapper($matcher, $this->getInvocationsContainer());
        }
        if ( ! $matcher instanceof MatcherInvocation) {
            throw InvalidArgumentHelper::factory(
                1,
                sprintf('object implementing interface %s\Matcher\Invocation', __NAMESPACE__),
                $matcher
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
    public function verify()
    {
        $this->getInvocationMocker()
            ->verify();
    }

    /**
     * @return  InvocationMocker
     */
    public function getInvocationMocker()
    {
        if ($this->invocationMocker === NULL) {
            $this->invocationMocker = new InvocationMocker;
        }
        return $this->invocationMocker;
    }

    /**
     * @return  InvocationMocker
     */
    protected function getInvocationsContainer()
    {
        if ($this->invocationsContainer === NULL) {
            $this->invocationsContainer = new InvocationsContainer;
        }
        return $this->invocationsContainer;
    }

    /**
     * @return  boolean
     */
    public function getRequireMatch()
    {
        return $this->getInvocationMocker()
            ->getRequireMatch();
    }

    /**
     * @param   boolean  $value
     * @return  $this
     */
    public function setRequireMatch($value)
    {
        $this->getInvocationMocker()
            ->setRequireMatch($value);
        return $this;
    }
}
