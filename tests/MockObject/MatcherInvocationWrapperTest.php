<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase,
    PHPUnit\Framework\MockObject\Invocation as MockObjectInvocation,
    PHPUnit\Framework\MockObject\Rule\InvocationOrder;

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
    public function testInvoked(BaseInvocation $baseInvocation, MockObjectInvocation $wrappedInvocation): void
    {
        $invocation = $this->createInvocationOrder();
        $object = $this->createObject(
            $invocation,
            $this->createContainer($baseInvocation, $wrappedInvocation)
        );
        $object->invoked($baseInvocation);
        $this->assertSame(1, $invocation->getInvocationCount());
    }

    public function provideInvoked(): array
    {
        return [
            [
                $this->createMock(BaseInvocation::class),
                $this->createMockObjectInvocation(),
            ],
        ];
    }

    /**
     * @dataProvider  provideMatches
     */
    public function testMatches(BaseInvocation $baseInvocation, MockObjectInvocation $wrappedInvocation, bool $expected): void
    {
        $invocation = $this->createInvocationOrder();
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

    public function provideMatches(): array
    {
        return [
            [
                $this->createMock(BaseInvocation::class),
                $this->createMockObjectInvocation(),
                TRUE,
            ],
            [
                $this->createMock(BaseInvocation::class),
                $this->createMockObjectInvocation(),
                FALSE,
            ],
        ];
    }

    /**
     * @test
     */
    public function testVerify(): void
    {
        $invocation = $this->createInvocationOrder();
        $invocation->expects($this->once())
            ->method('verify');
        $object = $this->createObject($invocation);
        $object->verify();
    }

    /**
     * @dataProvider  provideIsAnyInvokedCount
     */
    public function testIsAnyInvokedCount(InvocationOrder $invocation, bool $expected): void
    {
        $object = $this->createObject($invocation);
        $actual = $object->isAnyInvokedCount();
        $this->assertSame($expected, $actual);
    }

    public function provideIsAnyInvokedCount(): array
    {
        return [
            [
                $this->createMock(InvocationOrder::class),
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
    public function testIsNeverInvokedCount(InvocationOrder $invocation, bool $expected): void
    {
        $object = $this->createObject($invocation);
        $actual = $object->isNeverInvokedCount();
        $this->assertSame($expected, $actual);
    }

    public function provideIsNeverInvokedCount(): array
    {
        return [
            [
                $this->createMock(InvocationOrder::class),
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

    private function createContainer(
        BaseInvocation $baseInvocation,
        MockObjectInvocation $wrappedInvocation
    ): InvocationsContainer
    {
        $object = $this->createMock(InvocationsContainer::class);
        $object->expects($this->once())
            ->method('getMockObjectInvocation')
            ->with($baseInvocation)
            ->willReturn($wrappedInvocation);
        return $object;
    }

    private function createInvocationOrder(): InvocationOrder
    {
        return $this->getMockForAbstractClass(InvocationOrder::class);
    }

    private function createMockObjectInvocation(): MockObjectInvocation
    {
        return new MockObjectInvocation('', '', [], '', $this);
    }

    private function createObject(
        InvocationOrder $invocation,
        InvocationsContainer $container = NULL
    ): MatcherInvocationWrapper
    {
        if ($container === NULL) {
            $container = $this->createMock(InvocationsContainer::class);
        }
        return new MatcherInvocationWrapper($invocation, $container);
    }
}
