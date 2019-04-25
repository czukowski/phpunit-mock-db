<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Matcher\QueryMatcher,
    Cz\PHPUnit\MockDB\MockObject\InvocationsContainer,
    Cz\PHPUnit\MockDB\MockObject\MatcherInvocationWrapper,
    Cz\PHPUnit\SQL\EqualsSQLQueriesConstraint,
    PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Framework\ExpectationFailedException,
    PHPUnit\Framework\MockObject\Matcher\Invocation as MockObjectMatcherInvocation,
    Throwable;

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
    public function testHasMatchers(RecordedInvocation $invocationMatcher, bool $expected): void
    {
        $object = new Matcher($invocationMatcher);
        $actual = $object->hasMatchers();
        $this->assertSame($expected, $actual);
    }

    public function provideHasMatchers(): array
    {
        return [
            [$this->createMatcherInvocationMock(FALSE), TRUE],
            [$this->createMatcherInvocationMock(TRUE), FALSE],
            [$this->createMatcherInvocationWrapper($this->any()), FALSE],
            [$this->createMatcherInvocationWrapper($this->once()), TRUE],
            [$this->createMatcherInvocationWrapper($this->never()), TRUE],
        ];
    }

    private function createMatcherInvocationMock(bool $isAnyInvokedCount): RecordedInvocation
    {
        $mock = $this->createMock(RecordedInvocation::class);
        $mock->expects($this->once())
            ->method('isAnyInvokedCount')
            ->willReturn($isAnyInvokedCount);
        return $mock;
    }

    private function createMatcherInvocationWrapper(MockObjectMatcherInvocation $invocationMatcher): MatcherInvocationWrapper
    {
        return new MatcherInvocationWrapper($invocationMatcher, new InvocationsContainer);
    }

    /**
     * @dataProvider  provideQueryMatcher
     */
    public function testQueryMatcher(Constraint $constraint): void
    {
        $object = new Matcher($this->createMock(RecordedInvocation::class));
        $this->assertFalse($object->hasQueryMatcher());
        $matcher = new QueryMatcher($constraint);
        $object->setQueryMatcher($matcher);
        $this->assertSame($matcher, $object->getQueryMatcher());
        $this->assertTrue($object->hasQueryMatcher());
        $this->expectException('RuntimeException');
        $object->setQueryMatcher($matcher);
    }

    public function provideQueryMatcher(): array
    {
        return [
            [$this->createMock(Constraint::class)],
            [$this->stringStartsWith('SELECT')],
            [new EqualsSQLQueriesConstraint('SELECT * FROM `t`')],
        ];
    }

    /**
     * @dataProvider  provideInvoked
     */
    public function testInvoked(Invocation $invocation, array $invocationMatcherSetup, ?array $stubSetup): void
    {
        $invocationMatcher = $this->createMock(RecordedInvocation::class);
        $this->setupMockObject($invocationMatcher, $invocationMatcherSetup);

        $object = new Matcher($invocationMatcher);
        if ($stubSetup !== NULL) {
            $stub = $this->createMock(Stub::class);
            $this->setupMockObject($stub, $stubSetup);
            $object->setStub($stub);
        }
        $actual = $object->invoked($invocation);
        $this->assertNull($actual);
    }

    public function provideInvoked(): array
    {
        return [
            $this->createInvokedTestCase(FALSE),
            $this->createInvokedTestCase(TRUE),
        ];
    }

    private function createInvokedTestCase(bool $withStub): array
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
            $withStub
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
    public function testMatches(
        Invocation $invocation,
        array $invocationMatcherSetup,
        ?array $queryMatcherSetup,
        bool $expected
    ): void
    {
        $invocationMatcher = $this->createMock(RecordedInvocation::class);
        $this->setupMockObject($invocationMatcher, $invocationMatcherSetup);

        $object = new Matcher($invocationMatcher);
        if ($queryMatcherSetup !== NULL) {
            $queryMatcher = $this->createMock(QueryMatcher::class);
            $this->setupMockObject($queryMatcher, $queryMatcherSetup);
            $object->setQueryMatcher($queryMatcher);
        }
        $actual = $object->matches($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches(): array
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

    private function createMatchesTestCase(
        bool $matchesInvocationMatcher,
        ?bool $matchesQueryMatcher,
        bool $expected
    ): array
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
    public function testVerifyInvocationMatcher(array $invocationMatcherSetup, ?Throwable $expected): void
    {
        $invocationMatcher = $this->createMock(RecordedInvocation::class);
        $this->setupMockObject($invocationMatcher, $invocationMatcherSetup);

        $object = new Matcher($invocationMatcher);
        $this->expectExceptionFromArgument($expected);
        $actual = $object->verify();
        $this->assertSame($expected, $actual);
    }

    public function provideVerifyInvocationMatcher(): array
    {
        return [
            $this->createVerifyInvocationMatcherTestCase(TRUE),
            $this->createVerifyInvocationMatcherTestCase(FALSE),
        ];
    }

    private function createVerifyInvocationMatcherTestCase(bool $willVerify): array
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
    public function testVerifyQueryMatcher(
        array $invocationMatcherSetup,
        array $queryMatcherSetup,
        ?Throwable $expected
    ): void
    {
        $invocationMatcher = $this->createMock(RecordedInvocation::class);
        $this->setupMockObject($invocationMatcher, $invocationMatcherSetup);

        $object = new Matcher($invocationMatcher);
        $queryMatcher = $this->createMock(QueryMatcher::class);
        $this->setupMockObject($queryMatcher, $queryMatcherSetup);
        $object->setQueryMatcher($queryMatcher);
        $this->expectExceptionFromArgument($expected);

        $actual = $object->verify();
        $this->assertSame($expected, $actual);
    }

    public function provideVerifyQueryMatcher(): array
    {
        return [
            $this->createVerifyQueryMatcherTestCase(FALSE, FALSE, TRUE),
            $this->createVerifyQueryMatcherTestCase(FALSE, FALSE, FALSE),
            $this->createVerifyQueryMatcherTestCase(TRUE, FALSE, NULL),
            $this->createVerifyQueryMatcherTestCase(TRUE, TRUE, NULL),
            $this->createVerifyQueryMatcherTestCase(FALSE, TRUE, NULL),
        ];
    }

    private function createVerifyQueryMatcherTestCase(bool $isAny, bool $isNever, ?bool $willVerify): array
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

    private function createInvocationMock(): Invocation
    {
        return $this->createMock(Invocation::class);
    }

    private function setupMockObject($object, array $setup): void
    {
        foreach ($setup as $method => $invocations) {
            foreach ($invocations as $invocation) {
                $im = $object->expects($invocation['expects'])
                    ->method($method);
                if (isset($invocation['with'])) {
                    $im->with(...$invocation['with']);
                }
                if (isset($invocation['will'])) {
                    $im->will($invocation['will']);
                }
            }
        }
    }
}
