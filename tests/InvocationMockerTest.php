<?php
namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Matcher\Invocation as MatcherInvocation,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException,
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
    public function testAddMatcher($matchers)
    {
        $object = $this->createObject();
        foreach ($matchers as $matcher) {
            $object->addMatcher($matcher);
        }
        $actual = $this->getObjectAttribute($object, 'matchers');
        $this->assertSame($matchers, $actual);
    }

    public function provideAddMatcher()
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
    public function testHasMatchers($matchers, $expected)
    {
        $object = $this->createObject();
        foreach ($matchers as $matcher) {
            $object->addMatcher($matcher);
        }
        $actual = $object->hasMatchers();
        $this->assertSame($expected, $actual);
    }

    public function provideHasMatchers()
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
    public function testGetRequireMatch($value)
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
    public function testSetRequireMatch($value)
    {
        $object = $this->createObject();
        $object->setRequireMatch($value);
        $actual = $this->getObjectAttribute($object, 'requireMatch');
        $this->assertSame($value, $actual);
    }

    public function provideRequireMatch()
    {
        return [
            [TRUE],
            [FALSE],
        ];
    }

    /**
     * @dataProvider  provideExpects
     */
    public function testExpects($matcher)
    {
        $object = $this->createObject();
        $builder = $object->expects($matcher);
        $this->assertInstanceOf(InvocationMockerBuilder::class, $builder);
        // Risky part, actually testing implementation of other classes...
        $matcherWrapper = $this->getObjectAttribute($builder, 'matcher');
        $this->assertInstanceOf(Matcher::class, $matcherWrapper);
        $actual = $this->getObjectAttribute($matcherWrapper, 'invocationMatcher');
        $this->assertSame($matcher, $actual);
    }

    public function provideExpects()
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
    public function testInvoke($requireMatch, $matchers, $invocation, $expected)
    {
        $object = $this->createObject($matchers);
        $object->setRequireMatch($requireMatch);
        $this->expectExceptionFromArgument($expected);
        $actual = $object->invoke($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideInvoke()
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

    private function createInvokeTestCase($requireMatch, array $matchersWillMatch, $expected)
    {
        $invocation = $this->createMock(Invocation::class);
        $invocation->expects($this->any())
            ->method('getParameters')
            ->willReturn([]);
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
    public function testMatches($matchers, $invocation, $expected)
    {
        $object = $this->createObject($matchers);
        $actual = $object->matches($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches()
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

    private function createMatchesTestCase(array $matchersWillMatch, $expected)
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
    public function testVerify($matchers)
    {
        $object = $this->createObject($matchers);
        $actual = $object->verify();
        $this->assertNull($actual);
    }

    public function provideVerify()
    {
        return [
            $this->createVerifyTestCase(1),
            $this->createVerifyTestCase(2),
        ];
    }

    private function createVerifyTestCase($matchersCount)
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

    private function createObject(array $matchers = [])
    {
        $object = new InvocationMocker;
        foreach ($matchers as $matcher) {
            $object->addMatcher($matcher);
        }
        return $object;
    }
}
