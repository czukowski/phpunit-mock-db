<?php
namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase,
    PHPUnit_Framework_MockObject_Matcher_Invocation as MockObjectMatcherInvocation;

/**
 * MatcherInvocationWrapperTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MatcherInvocationWrapperTest extends Testcase
{
    /**
     * @dataProvider  provideInvoked
     */
    public function testInvoked($baseInvocation, $wrappedInvocation)
    {
        $invocation = $this->createMatcherInvocation();
        $invocation->expects($this->once())
            ->method('invoked')
            ->with($wrappedInvocation);
        $object = $this->createObject(
            $invocation,
            $this->createContainer($baseInvocation, $wrappedInvocation)
        );
        $object->invoked($baseInvocation);
    }

    public function provideInvoked()
    {
        return [
            [
                $this->createMock('Cz\PHPUnit\MockDB\Invocation'),
                $this->createMock('Cz\PHPUnit\MockDB\MockObject\InvocationWrapper'),
            ],
        ];
    }

    /**
     * @dataProvider  provideMatches
     */
    public function testMatches($baseInvocation, $wrappedInvocation, $expected)
    {
        $invocation = $this->createMatcherInvocation();
        $invocation->expects($this->once())
            ->method('matches')
            ->with($wrappedInvocation)
            ->willReturn($expected);
        $object = $this->createObject(
            $invocation,
            $this->createContainer($baseInvocation, $wrappedInvocation)
        );
        $actual = $object->matches($baseInvocation);
        $this->assertSame($expected, $actual);
    }

    public function provideMatches()
    {
        return [
            [
                $this->createMock('Cz\PHPUnit\MockDB\Invocation'),
                $this->createMock('Cz\PHPUnit\MockDB\MockObject\InvocationWrapper'),
                TRUE,
            ],
            [
                $this->createMock('Cz\PHPUnit\MockDB\Invocation'),
                $this->createMock('Cz\PHPUnit\MockDB\MockObject\InvocationWrapper'),
                FALSE,
            ],
        ];
    }

    /**
     * @test
     */
    public function testVerify()
    {
        $invocation = $this->createMatcherInvocation();
        $invocation->expects($this->once())
            ->method('verify');
        $object = $this->createObject($invocation);
        $object->verify();
    }

    /**
     * @dataProvider  provideIsAnyInvokedCount
     */
    public function testIsAnyInvokedCount($invocation, $expected)
    {
        $object = $this->createObject($invocation);
        $actual = $object->isAnyInvokedCount();
        $this->assertSame($expected, $actual);
    }

    public function provideIsAnyInvokedCount()
    {
        return [
            [
                $this->createMock('PHPUnit_Framework_MockObject_Matcher_Invocation'),
                FALSE,
            ],
            [
                $this->once(),
                FALSE,
            ],
            [
                $this->at(0),
                FALSE,
            ],
            [
                $this->never(),
                FALSE,
            ],
            [
                $this->any(),
                TRUE,
            ],
        ];
    }

    /**
     * @dataProvider  provideIsNeverInvokedCount
     */
    public function testIsNeverInvokedCount($invocation, $expected)
    {
        $object = $this->createObject($invocation);
        $actual = $object->isNeverInvokedCount();
        $this->assertSame($expected, $actual);
    }

    public function provideIsNeverInvokedCount()
    {
        return [
            [
                $this->createMock('PHPUnit_Framework_MockObject_Matcher_Invocation'),
                FALSE,
            ],
            [
                $this->once(),
                FALSE,
            ],
            [
                $this->at(0),
                FALSE,
            ],
            [
                $this->any(),
                FALSE,
            ],
            [
                $this->never(),
                TRUE,
            ],
            [
                $this->exactly(0),
                TRUE,
            ],
        ];
    }

    private function createContainer($baseInvocation, $wrappedInvocation)
    {
        $object = $this->createMock('Cz\PHPUnit\MockDB\MockObject\InvocationsContainer');
        $object->expects($this->once())
            ->method('getMockObjectInvocation')
            ->with($baseInvocation)
            ->willReturn($wrappedInvocation);
        return $object;
    }

    private function createMatcherInvocation()
    {
        return $this->createMock('PHPUnit_Framework_MockObject_Matcher_Invocation');
    }

    private function createObject(MockObjectMatcherInvocation $invocation, InvocationsContainer $container = NULL)
    {
        if ($container === NULL) {
            $container = $this->createMock('Cz\PHPUnit\MockDB\MockObject\InvocationsContainer');
        }
        return new MatcherInvocationWrapper($invocation, $container);
    }
}
