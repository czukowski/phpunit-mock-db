<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase,
    PHPUnit\Framework\Constraint\Constraint;

/**
 * QueryMatcherTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class QueryMatcherTest extends Testcase
{
    /**
     * @dataProvider  provideMatches
     */
    public function testMatches(string $query, bool $expected): void
    {
        $constraint = $this->createMock(Constraint::class);
        $constraint->expects($this->once())
            ->method('evaluate')
            ->with($query, '', TRUE)
            ->willReturn($expected);
        $invocation = $this->createMock(BaseInvocation::class);
        $invocation->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $object = new QueryMatcher($constraint);
        $actual = $object->matches($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches(): array
    {
        return [
            ['SELECT * FROM `t1`', TRUE],
            ['SELECT * FROM `t2`', FALSE],
        ];
    }
}
