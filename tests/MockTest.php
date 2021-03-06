<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Invocation\QueryInvocation,
    Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\MockObject\InvocationsContainer,
    Cz\PHPUnit\MockDB\MockObject\MatcherInvocationWrapper,
    LogicException,
    PHPUnit\Framework\Exception as FrameworkException,
    PHPUnit\Framework\MockObject\Rule\InvocationOrder;

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
    public function testExpects($argument, $expected): void
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

    public function provideExpects(): array
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

    private function createExpectsTestCaseException($value): array
    {
        return [$value, new FrameworkException];
    }

    private function createExpectsTestCaseMockDbInvocationMatcher(MatcherInvocation $matcher): array
    {
        return [$matcher, $matcher];
    }

    private function createExpectsTestCaseWrappedInvocationMatcher(InvocationOrder $matcher): array
    {
        return [
            $matcher,
            function ($actual, $container) use ($matcher) {
                $this->assertInstanceOf(MatcherInvocationWrapper::class, $actual);
                $this->assertSame($container, $this->getObjectPropertyValue($actual, 'container'));
                $this->assertSame($matcher, $this->getObjectPropertyValue($actual, 'invocation'));
            }
        ];
    }

    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke(array $arguments, $expected): void
    {
        $invocationMocker = new Doubles\InvocationMockerDouble;
        $invocationMocker->setRequireMatch(FALSE);
        $object = new Doubles\MockDouble($invocationMocker);

        $this->expectExceptionFromArgument($expected);
        $actual = $object->invoke(...$arguments);
        $this->assertSame($invocationMocker->invoked, $actual);
        if (is_callable($expected)) {
            call_user_func($expected, $actual);
        }
        else {
            $this->assertSame($expected, $actual);
        }
    }

    public function provideInvoke(): array
    {
        return [
            $this->createInvokeTestCaseString('SELECT * FROM `t`', []),
            $this->createInvokeTestCaseString('SELECT * FROM `t` WHERE `c` = ?', [1]),
            $this->createInvokeTestCaseInvocationInstance('SELECT * FROM `t`'),
            $this->createInvokeTestCaseInvocationInstanceAnd2ndArgument('SELECT * FROM `t`', []),
        ];
    }

    private function createInvokeTestCaseString(string $query, array $parameters): array
    {
        return [
            [$query, $parameters],
            function ($actual) use ($query, $parameters) {
                $this->assertInstanceOf(QueryInvocation::class, $actual);
                $this->assertSame($query, $actual->getQuery());
                $this->assertSame($parameters, $actual->getParameters());
            }
        ];
    }

    private function createInvokeTestCaseInvocationInstance(string $query): array
    {
        $invocation = new QueryInvocation($query);
        return [[$invocation], $invocation];
    }

    private function createInvokeTestCaseInvocationInstanceAnd2ndArgument(string $query, array $parameters): array
    {
        return [
            [new QueryInvocation($query), $parameters],
            new LogicException,
        ];
    }

    /**
     * @dataProvider  provideRequireMatch
     */
    public function testGetRequireMatch(bool $value): void
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
    public function testSetRequireMatch(bool $value): void
    {
        $invocationMocker = new Doubles\InvocationMockerDouble;
        $object = new Doubles\MockDouble($invocationMocker);

        $actual = $object->setRequireMatch($value);
        $this->assertSame($object, $actual);
        $this->assertSame($value, $invocationMocker->getRequireMatch());
    }

    public function provideRequireMatch(): array
    {
        return [
            [TRUE],
            [FALSE],
        ];
    }
}
