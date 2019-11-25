<?php
namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase;

/**
 * AnyParametersTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class AnyParametersTest extends Testcase
{
    public function testToString()
    {
        $object = $this->createObject();
        $actual = $object->toString();
        $this->assertSame('with any parameters', $actual);
    }

    /**
     * @dataProvider  provideMatches
     */
    public function testMatches($parameters, $expected)
    {
        $object = $this->createObject();
        $invocation = $this->createMock(BaseInvocation::class);
        $invocation->expects($this->once())
            ->method('getParameters')
            ->willReturn($parameters);
        $actual = $object->matches($invocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches()
    {
        return [
            [[], FALSE],
            [[1], TRUE],
        ];
    }

    public function testInvoked()
    {
        $object = $this->createObject();
        $invocation = $this->createMock(BaseInvocation::class);
        $actual = $object->invoked($invocation);
        $this->assertNull($actual);
    }

    public function testVerify()
    {
        $object = $this->createObject();
        $actual = $object->verify();
        $this->assertNull($actual);
    }

    private function createObject()
    {
        return new AnyParameters;
    }
}
