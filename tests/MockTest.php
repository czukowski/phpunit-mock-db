<?php
namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Invocation\QueryInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\MockObject\InvocationsContainer,
    Cz\PHPUnit\MockDB\MockObject\MatcherInvocationWrapper,
    PHPUnit_Framework_Exception as FrameworkException;

/**
 * MockTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MockTest extends Testcase
{
    /**
     * @dataProvider  provideExpects
     */
    public function testExpects($argument, $expected)
    {
        // Using test double classes to avoid having to mock methods named 'expects'.
        $invocationMocker = new Doubles\InvocationMockerDouble;
        $container = new InvocationsContainer;
        $object = new Doubles\MockDouble($invocationMocker, $container);

        $this->expectExceptionFromArgument($expected);
        $builder = $object->expects($argument);

        $this->assertInstanceOf(InvocationMockerBuilder::class, $builder);
        $actual = $invocationMocker->matcher;
        if (is_callable($expected)) {
            call_user_func($expected, $actual, $container);
        }
        else {
            $this->assertSame($expected, $actual);
        }
    }

    public function provideExpects()
    {
        return [
            $this->createExpectsTestCaseException(NULL),
            $this->createExpectsTestCaseException(3.14),
            $this->createExpectsTestCaseException('foo'),
            $this->createExpectsTestCaseMockDbInvocationMatcher($this->createMock(RecordedInvocation::class)),
            $this->createExpectsTestCaseWrappedInvocationMatcher($this->any()),
            $this->createExpectsTestCaseWrappedInvocationMatcher($this->once()),
            $this->createExpectsTestCaseWrappedInvocationMatcher($this->never()),
        ];
    }

    private function createExpectsTestCaseException($value)
    {
        return [$value, new FrameworkException];
    }

    private function createExpectsTestCaseMockDbInvocationMatcher($matcher)
    {
        return [$matcher, $matcher];
    }

    private function createExpectsTestCaseWrappedInvocationMatcher($matcher)
    {
        return [
            $matcher,
            function ($actual, $container) use ($matcher) {
                $this->assertInstanceOf(MatcherInvocationWrapper::class, $actual);
                $this->assertSame($container, $this->getObjectAttribute($actual, 'container'));
                $this->assertSame($matcher, $this->getObjectAttribute($actual, 'invocation'));
            }
        ];
    }

    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke($query, $expected)
    {
        $invocationMocker = new Doubles\InvocationMockerDouble;
        $invocationMocker->setRequireMatch(FALSE);
        $object = new Doubles\MockDouble($invocationMocker);

        $actual = $object->invoke($query);
        $this->assertSame($invocationMocker->invoked, $actual);
        if (is_callable($expected)) {
            call_user_func($expected, $actual);
        }
        else {
            $this->assertSame($expected, $actual);
        }
    }

    public function provideInvoke()
    {
        return [
            $this->createInvokeTestCaseString('SELECT * FROM `t`'),
            $this->createInvokeTestCaseInvocationInstance('SELECT * FROM `t`'),
        ];
    }

    private function createInvokeTestCaseString($query)
    {
        return [
            $query,
            function ($actual) use ($query) {
                $this->assertInstanceOf(QueryInvocation::class, $actual);
                $this->assertSame($query, $actual->getQuery());
            }
        ];
    }

    private function createInvokeTestCaseInvocationInstance($query)
    {
        $invocation = new QueryInvocation($query);
        return [$invocation, $invocation];
    }

    /**
     * @dataProvider  provideRequireMatch
     */
    public function testGetRequireMatch($value)
    {
        $invocationMocker = new Doubles\InvocationMockerDouble;
        $invocationMocker->setRequireMatch($value);
        $object = new Doubles\MockDouble($invocationMocker);

        $actual = $object->getRequireMatch();
        $this->assertSame($value, $actual);
    }

    /**
     * @dataProvider  provideRequireMatch
     */
    public function testSetRequireMatch($value)
    {
        $invocationMocker = new Doubles\InvocationMockerDouble;
        $object = new Doubles\MockDouble($invocationMocker);

        $actual = $object->setRequireMatch($value);
        $this->assertSame($object, $actual);
        $this->assertSame($value, $invocationMocker->getRequireMatch());
    }

    public function provideRequireMatch()
    {
        return [
            [TRUE],
            [FALSE],
        ];
    }
}
