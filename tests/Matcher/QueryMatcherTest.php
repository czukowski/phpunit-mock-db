<?php
namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase,
    PHPUnit_Framework_Constraint as Constraint;

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
    public function testMatches($query, $expected)
    {
        $constraint = $this->createMock('PHPUnit_Framework_Constraint');
        $constraint->expects($this->once())
            ->method('evaluate')
            ->with($query, '', TRUE)
            ->willReturn($expected);
        $invocation = $this->createMock('Cz\PHPUnit\MockDB\Invocation');
        $invocation->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $object = new QueryMatcher($constraint);
        $actual = $object->matches($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches()
    {
        return [
            ['SELECT * FROM `t1`', TRUE],
            ['SELECT * FROM `t2`', FALSE],
        ];
    }
}
