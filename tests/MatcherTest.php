<?php
namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Matcher\QueryMatcher,
    Cz\PHPUnit\MockDB\MockObject\InvocationsContainer,
    Cz\PHPUnit\MockDB\MockObject\MatcherInvocationWrapper,
    Cz\PHPUnit\SQL\EqualsSQLQueriesConstraint,
    PHPUnit_Framework_Constraint as Constraint,
    PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException;

/**
 * MatcherTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MatcherTest extends Testcase
{
    /**
     * @dataProvider  provideHasMatchers
     */
    public function testHasMatchers($invocationMatcher, $expected)
    {
        $object = new Matcher($invocationMatcher);
        $actual = $object->hasMatchers();
        $this->assertSame($expected, $actual);
    }

    public function provideHasMatchers()
    {
        return [
            [$this->createMatcherInvocationMock(FALSE), TRUE],
            [$this->createMatcherInvocationMock(TRUE), FALSE],
            [$this->createMatcherInvocationWrapper($this->any()), FALSE],
            [$this->createMatcherInvocationWrapper($this->once()), TRUE],
            [$this->createMatcherInvocationWrapper($this->never()), TRUE],
        ];
    }

    private function createMatcherInvocationMock($isAnyInvokedCount)
    {
        $mock = $this->createMock('Cz\PHPUnit\MockDB\Matcher\RecordedInvocation');
        $mock->expects($this->once())
            ->method('isAnyInvokedCount')
            ->willReturn($isAnyInvokedCount);
        return $mock;
    }

    private function createMatcherInvocationWrapper($invocationMatcher)
    {
        return new MatcherInvocationWrapper($invocationMatcher, new InvocationsContainer);
    }

    /**
     * @dataProvider  provideQueryMatcher
     */
    public function testQueryMatcher($constraint)
    {
        $object = new Matcher($this->createMock('Cz\PHPUnit\MockDB\Matcher\RecordedInvocation'));
        $this->assertNull($object->getQueryMatcher());
        $this->assertFalse($object->hasQueryMatcher());
        $matcher = new QueryMatcher($constraint);
        $object->setQueryMatcher($matcher);
        $this->assertSame($matcher, $object->getQueryMatcher());
        $this->assertTrue($object->hasQueryMatcher());
        $this->expectException('RuntimeException');
        $object->setQueryMatcher($matcher);
    }

    public function provideQueryMatcher()
    {
        return [
            [$this->createMock('PHPUnit_Framework_Constraint')],
            [$this->stringStartsWith('SELECT')],
            [new EqualsSQLQueriesConstraint('SELECT * FROM `t`')],
        ];
    }

    /**
     * @dataProvider  provideInvoked
     */
    public function testInvoked($invocation, $invocationMatcherSetup, $stubSetup)
    {
        $invocationMatcher = $this->createMock('Cz\PHPUnit\MockDB\Matcher\RecordedInvocation');
        $this->setupMockObject($invocationMatcher, $invocationMatcherSetup);

        $object = new Matcher($invocationMatcher);
        if ($stubSetup !== NULL) {
            $stub = $this->createMock('Cz\PHPUnit\MockDB\Stub');
            $this->setupMockObject($stub, $stubSetup);
            $object->setStub($stub);
        }
        $actual = $object->invoked($invocation);
        $this->assertNull($actual);
    }

    public function provideInvoked()
    {
        return [
            $this->createInvokedTestCase(FALSE),
            $this->createInvokedTestCase(TRUE),
        ];
    }

    private function createInvokedTestCase($withStub)
    {
        $invocation = $this->createInvocationMock();
        return [
            $invocation,
            [
                'invoked' => [
                    [
                        'expects' => $this->once(),
                        'with' => [$invocation],
                    ],
                ],
            ],
            $withStub === NULL
                ? NULL
                : [
                      'invoke' => [
                          [
                              'expects' => $this->once(),
                              'with' => [$invocation],
                          ],
                      ],
                  ],
        ];
    }

    /**
     * @dataProvider  provideMatches
     */
    public function testMatches($invocation, $invocationMatcherSetup, $queryMatcherSetup, $expected)
    {
        $invocationMatcher = $this->createMock('Cz\PHPUnit\MockDB\Matcher\RecordedInvocation');
        $this->setupMockObject($invocationMatcher, $invocationMatcherSetup);

        $object = new Matcher($invocationMatcher);
        if ($queryMatcherSetup !== NULL) {
            $queryMatcher = $this->createMock('Cz\PHPUnit\MockDB\Matcher\QueryMatcher');
            $this->setupMockObject($queryMatcher, $queryMatcherSetup);
            $object->setQueryMatcher($queryMatcher);
        }
        $actual = $object->matches($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches()
    {
        return [
            $this->createMatchesTestCase(TRUE, NULL, TRUE),
            $this->createMatchesTestCase(TRUE, TRUE, TRUE),
            $this->createMatchesTestCase(TRUE, FALSE, FALSE),
            $this->createMatchesTestCase(FALSE, NULL, FALSE),
            $this->createMatchesTestCase(FALSE, TRUE, FALSE),
            $this->createMatchesTestCase(FALSE, FALSE, FALSE),
        ];
    }

    private function createMatchesTestCase($matchesInvocationMatcher, $matchesQueryMatcher, $expected)
    {
        $invocation = $this->createInvocationMock();
        return [
            $invocation,
            [
                'matches' => [
                    [
                        'expects' => $this->once(),
                        'with' => [$invocation],
                        'will' => $this->returnValue($matchesInvocationMatcher),
                    ]
                ],
            ],
            $matchesQueryMatcher === NULL
                ? NULL
                : [
                      'matches' => [
                          [
                              'expects' => $matchesInvocationMatcher ? $this->once() : $this->never(),
                              'with' => [$invocation],
                              'will' => $this->returnValue($matchesQueryMatcher),
                          ],
                      ],
                  ],
            $expected,
        ];
    }

    /**
     * @dataProvider  provideVerifyInvocationMatcher
     */
    public function testVerifyInvocationMatcher($invocationMatcherSetup, $expected)
    {
        $invocationMatcher = $this->createMock('Cz\PHPUnit\MockDB\Matcher\RecordedInvocation');
        $this->setupMockObject($invocationMatcher, $invocationMatcherSetup);

        $object = new Matcher($invocationMatcher);
        $this->expectExceptionFromArgument($expected);
        $actual = $object->verify();
        $this->assertSame($expected, $actual);
    }

    public function provideVerifyInvocationMatcher()
    {
        return [
            $this->createVerifyInvocationMatcherTestCase(TRUE),
            $this->createVerifyInvocationMatcherTestCase(FALSE),
        ];
    }

    private function createVerifyInvocationMatcherTestCase($willVerify)
    {
        return [
            [
                'verify' => [
                    [
                        'expects' => $this->once(),
                        'will' => $willVerify
                            ? $this->returnValue(NULL)
                            : $this->throwException(new ExpectationFailedException('Invocation matcher failed')),
                    ],
                ],
            ],
            $willVerify ? NULL : new ExpectationFailedException(''),
        ];
    }

    /**
     * @dataProvider  provideVerifyQueryMatcher
     */
    public function testVerifyQueryMatcher($invocationMatcherSetup, $queryMatcherSetup, $expected)
    {
        $invocationMatcher = $this->createMock('Cz\PHPUnit\MockDB\Matcher\RecordedInvocation');
        $this->setupMockObject($invocationMatcher, $invocationMatcherSetup);

        $object = new Matcher($invocationMatcher);
        $queryMatcher = $this->createMock('Cz\PHPUnit\MockDB\Matcher\QueryMatcher');
        $this->setupMockObject($queryMatcher, $queryMatcherSetup);
        $object->setQueryMatcher($queryMatcher);
        $this->expectExceptionFromArgument($expected);

        $actual = $object->verify();
        $this->assertSame($expected, $actual);
    }

    public function provideVerifyQueryMatcher()
    {
        return [
            $this->createVerifyQueryMatcherTestCase(FALSE, FALSE, TRUE),
            $this->createVerifyQueryMatcherTestCase(FALSE, FALSE, FALSE),
            $this->createVerifyQueryMatcherTestCase(TRUE, FALSE, NULL),
            $this->createVerifyQueryMatcherTestCase(TRUE, TRUE, NULL),
            $this->createVerifyQueryMatcherTestCase(FALSE, TRUE, NULL),
        ];
    }

    private function createVerifyQueryMatcherTestCase($isAny, $isNever, $willVerify)
    {
        return [
            [
                'verify' => [
                    [
                        'expects' => $this->once(),
                    ],
                ],
                'isAnyInvokedCount' => [
                    [
                        'expects' => $this->once(),
                        'will' => $this->returnValue($isAny),
                    ],
                ],
                'isNeverInvokedCount' => [
                    [
                        'expects' => ! $isAny ? $this->once() : $this->never(),
                        'will' => $this->returnValue($isNever),
                    ],
                ],
            ],
            [
                'verify' => [
                    [
                        'expects' => ! $isAny && ! $isNever ? $this->once() : $this->never(),
                        'will' => $willVerify
                            ? $this->returnValue(NULL)
                            : $this->throwException(new ExpectationFailedException('Query matcher failed')),
                    ],
                ],
            ],
            ! $isAny && ! $isNever && $willVerify ? NULL : new ExpectationFailedException(''),
        ];
    }

    private function createInvocationMock()
    {
        return $this->createMock('Cz\PHPUnit\MockDB\Invocation');
    }

    private function setupMockObject($object, array $setup)
    {
        foreach ($setup as $method => $invocations) {
            foreach ($invocations as $invocation) {
                $im = $object->expects($invocation['expects'])
                    ->method($method);
                if (isset($invocation['with'])) {
                    call_user_func_array([$im, 'with'], $invocation['with']);
                }
                if (isset($invocation['will'])) {
                    $im->will($invocation['will']);
                }
            }
        }
    }
}
