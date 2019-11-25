<?php
namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase,
    PHPUnit\Framework\ExpectationFailedException;

/**
 * ParametersMatchTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ParametersMatchTest extends Testcase
{
    /**
     * @dataProvider  provideMatches
     */
    public function testMatches($parameters, $constraints, $expected)
    {
        $invocation = $this->createInvocation($parameters);
        $object = $this->createObject($constraints);
        $this->expectExceptionFromArgument($expected);
        $actual = $object->matches($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches()
    {
        return [
            'Single parameter match' => [
                [1],
                [1],
                TRUE,
            ],
            'Multiple parameters match' => [
                [1, 2],
                [1, 2],
                TRUE,
            ],
            'Expectation failure on 1st parameter' => [
                [1],
                [2],
                FALSE,
            ],
            'Expectation failure on latter parameter' => [
                [1, 2],
                [1, 3],
                FALSE,
            ],
            'Invoked without parameters while "anything" expected' => [
                [],
                [$this->anything()],
                new ExpectationFailedException(''),
            ],
            'Invoked with less parameters than expected' => [
                [1],
                [1, 2],
                new ExpectationFailedException(''),
            ],
            'Invoked with more parameters than expected (omitted expectations)' => [
                [1, 2, 3],
                [1, 2],
                TRUE,
            ],
        ];
    }

    public function testInvoked()
    {
        $object = $this->createObject([]);
        $invocation = $this->createMock(BaseInvocation::class);
        $actual = $object->invoked($invocation);
        $this->assertNull($actual);
    }

    public function testVerify()
    {
        $object = $this->createObject([]);
        $actual = $object->verify();
        $this->assertNull($actual);
    }

    /**
     * @dataProvider  provideToString
     */
    public function testToString($constraints, $expected)
    {
        $object = $this->createObject($constraints);
        $actual = $object->toString();
        $this->assertSame($expected, $actual);
    }

    public function provideToString()
    {
        return [
            [
                [1, 2, 3],
                'with parameter 1 is identical to 1, parameter 2 is identical to 2 and parameter 3 is identical to 3',
            ],
            [
                [$this->anything()],
                'with parameter 1 is anything',
            ]
        ];
    }

    private function createInvocation($parameters)
    {
        $invocation = $this->createMock(BaseInvocation::class);
        $invocation->expects($this->once())
            ->method('getParameters')
            ->willReturn($parameters);
        return $invocation;
    }

    private function createObject($constraints)
    {
        return new ParametersMatch($constraints);
    }
}
