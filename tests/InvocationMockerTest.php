<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    PHPUnit\Framework\ExpectationFailedException,
    ReflectionProperty;

/**
 * InvocationMockerTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvocationMockerTest extends Testcase
{
    /**
     * @dataProvider  provideAddMatcher
     */
    public function testAddMatcher(array $matchers): void
    {
        $object = $this->createObject();
        foreach ($matchers as $matcher) {
            $object->addMatcher($matcher);
        }
        $actual = $this->getObjectPropertyValue($object, 'matchers');
        $this->assertSame($matchers, $actual);
    }

    public function provideAddMatcher(): array
    {
        return [
            [
                [
                    $this->createMock(RecordedInvocation::class),
                ],
            ],
            [
                [
                    $this->createMock(RecordedInvocation::class),
                    $this->createMock(RecordedInvocation::class),
                ],
            ],
        ];
    }

    /**
     * @dataProvider  provideHasMatchers
     */
    public function testHasMatchers(array $matchers, bool $expected): void
    {
        $object = $this->createObject();
        foreach ($matchers as $matcher) {
            $object->addMatcher($matcher);
        }
        $actual = $object->hasMatchers();
        $this->assertSame($expected, $actual);
    }

    public function provideHasMatchers(): array
    {
        return [
            [
                [],
                FALSE,
            ],
            [
                [
                    $this->createMock(RecordedInvocation::class),
                ],
                TRUE,
            ],
            [
                [
                    $this->createMock(RecordedInvocation::class),
                    $this->createMock(RecordedInvocation::class),
                ],
                TRUE,
            ],
        ];
    }

    /**
     * @dataProvider  provideRequireMatch
     */
    public function testGetRequireMatch(bool $value): void
    {
        $object = $this->createObject();
        $this->assertTrue($object->getRequireMatch());
        $requireMatch = new ReflectionProperty(InvocationMocker::class, 'requireMatch');
        $requireMatch->setAccessible(TRUE);
        $requireMatch->setValue($object, $value);
        $actual = $object->getRequireMatch();
        $this->assertSame($value, $actual);
    }


    /**
     * @dataProvider  provideRequireMatch
     */
    public function testSetRequireMatch(bool $value): void
    {
        $object = $this->createObject();
        $object->setRequireMatch($value);
        $actual = $this->getObjectPropertyValue($object, 'requireMatch');
        $this->assertSame($value, $actual);
    }

    public function provideRequireMatch(): array
    {
        return [
            [TRUE],
            [FALSE],
        ];
    }

    /**
     * @dataProvider  provideExpects
     */
    public function testExpects(MatcherInvocation $matcher): void
    {
        $object = $this->createObject();
        $builder = $object->expects($matcher);
        $this->assertInstanceOf(InvocationMockerBuilder::class, $builder);
        // Risky part, actually testing implementation of other classes...
        $matcherWrapper = $this->getObjectPropertyValue($builder, 'matcher');
        $this->assertInstanceOf(Matcher::class, $matcherWrapper);
        $actual = $this->getObjectPropertyValue($matcherWrapper, 'invocationMatcher');
        $this->assertSame($matcher, $actual);
    }

    public function provideExpects(): array
    {
        return [
            [
                $this->createMock(RecordedInvocation::class),
            ],
        ];
    }

    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke(
        bool $requireMatch,
        array $matchers,
        Invocation $invocation,
        ?ExpectationFailedException $expected
    ): void
    {
        $object = $this->createObject($matchers);
        $object->setRequireMatch($requireMatch);
        $this->expectExceptionFromArgument($expected);
        $actual = $object->invoke($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideInvoke(): array
    {
        return [
            $this->createInvokeTestCase(TRUE, [TRUE], NULL),
            $this->createInvokeTestCase(TRUE, [TRUE, TRUE], NULL),
            $this->createInvokeTestCase(TRUE, [FALSE, TRUE], NULL),
            $this->createInvokeTestCase(TRUE, [FALSE, FALSE], new ExpectationFailedException('')),
            $this->createInvokeTestCase(TRUE, [FALSE], new ExpectationFailedException('')),
            $this->createInvokeTestCase(FALSE, [FALSE, FALSE], NULL),
            $this->createInvokeTestCase(FALSE, [FALSE], NULL),
        ];
    }

    private function createInvokeTestCase(
        bool $requireMatch,
        array $matchersWillMatch,
        ?ExpectationFailedException $expected
    ): array
    {
        $invocation = $this->createMock(Invocation::class);
        return [
            $requireMatch,
            array_map(
                function ($willMatch) use ($invocation) {
                    $object = $this->createMock(MatcherInvocation::class);
                    $object->expects($this->once())
                        ->method('matches')
                        ->with($invocation)
                        ->willReturn($willMatch);
                    $object->expects($willMatch ? $this->once() : $this->never())
                        ->method('invoked')
                        ->with($invocation);
                    return $object;
                },
                $matchersWillMatch
            ),
            $invocation,
            $expected,
        ];
    }

    /**
     * @dataProvider  provideMatches
     */
    public function testMatches(array $matchers, Invocation $invocation, bool $expected): void
    {
        $object = $this->createObject($matchers);
        $actual = $object->matches($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches(): array
    {
        return [
            $this->createMatchesTestCase([TRUE], TRUE),
            $this->createMatchesTestCase([TRUE, TRUE], TRUE),
            $this->createMatchesTestCase([FALSE], FALSE),
            $this->createMatchesTestCase([TRUE, FALSE], FALSE),
            $this->createMatchesTestCase([FALSE, TRUE], FALSE),
            $this->createMatchesTestCase([FALSE, FALSE], FALSE),
        ];
    }

    private function createMatchesTestCase(array $matchersWillMatch, bool $expected): array
    {
        $invocation = $this->createMock(Invocation::class);
        $willSoFar = TRUE;
        return [
            array_map(
                function ($willMatch) use ($invocation, & $willSoFar) {
                    $object = $this->createMock(MatcherInvocation::class);
                    $object->expects($willSoFar ? $this->once() : $this->never())
                        ->method('matches')
                        ->with($invocation)
                        ->willReturn($willMatch);
                    $willSoFar &= $willMatch;  // Set to FALSE after first non-match.
                    return $object;
                },
                $matchersWillMatch
            ),
            $invocation,
            $expected,
        ];
    }

    /**
     * @dataProvider  provideVerify
     */
    public function testVerify(array $matchers): void
    {
        $object = $this->createObject($matchers);
        $actual = $object->verify();
        $this->assertNull($actual);
    }

    public function provideVerify(): array
    {
        return [
            $this->createVerifyTestCase(1),
            $this->createVerifyTestCase(2),
        ];
    }

    private function createVerifyTestCase(int $matchersCount): array
    {
        return [
            array_map(
                function () {
                    $object = $this->createMock(MatcherInvocation::class);
                        $object->expects($this->once())
                            ->method('verify');
                        return $object;
                },
                array_fill(0, $matchersCount, NULL)
            ),
        ];
    }

    private function createObject(array $matchers = []): InvocationMocker
    {
        $object = new InvocationMocker;
        foreach ($matchers as $matcher) {
            $object->addMatcher($matcher);
        }
        return $object;
    }
}
