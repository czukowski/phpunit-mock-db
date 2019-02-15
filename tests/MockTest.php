<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Invocation\QueryInvocation,
    Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\MockObject\InvocationsContainer,
    Cz\PHPUnit\MockDB\MockObject\MatcherInvocationWrapper,
    PHPUnit\Framework\Exception as FrameworkException,
    PHPUnit\Framework\MockObject\Matcher\Invocation as MockObjectMatcherInvocation;

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

    private function createExpectsTestCaseWrappedInvocationMatcher(MockObjectMatcherInvocation $matcher): array
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
    public function testInvoke($query, $expected): void
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

    public function provideInvoke(): array
    {
        return [
            $this->createInvokeTestCaseString('SELECT * FROM `t`'),
            $this->createInvokeTestCaseInvocationInstance('SELECT * FROM `t`'),
        ];
    }

    private function createInvokeTestCaseString(string $query): array
    {
        return [
            $query,
            function ($actual) use ($query) {
                $this->assertInstanceOf(QueryInvocation::class, $actual);
                $this->assertSame($query, $actual->getQuery());
            }
        ];
    }

    private function createInvokeTestCaseInvocationInstance(string $query): array
    {
        $invocation = new QueryInvocation($query);
        return [$invocation, $invocation];
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
