<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Stub,
    PHPUnit\Framework\MockObject\RuntimeException,
    Exception;

/**
 * ConsecutiveCallsStubTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ConsecutiveCallsStubTest extends Testcase
{
    /**
     * @dataProvider  provideAddStub
     */
    public function testAddStub(array $initialStack, Stub $stub): void
    {
        $object = new ConsecutiveCallsStub($initialStack);
        $initialStackCount = count($initialStack);
        $object->addStub($stub);
        $stack = $this->getObjectPropertyValue($object, 'stack');
        $actual = $stack[$initialStackCount];
        $this->assertSame($stub, $actual);
        $this->assertCount($initialStackCount + 1, $stack);
    }

    public function provideAddStub(): array
    {
        return [
            [
                [],
                $this->createMock(Stub::class),
            ],
            [
                [
                    $this->createMock(Stub::class),
                    $this->createMock(Stub::class),
                ],
                $this->createMock(Stub::class),
            ],
        ];
    }

    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke(array $stack, array $invocations): void
    {
        $object = new ConsecutiveCallsStub($stack);
        foreach ($invocations as $invocation) {
            if ($invocation instanceof Exception) {
                $this->expectExceptionObject($invocation);
                $invocation = $this->createMock(Invocation::class);
            }
            $object->invoke($invocation);
        }
    }

    public function provideInvoke(): array
    {
        $resultSet1 = [
            ['id' => 1, 'name' => 'foo'],
            ['id' => 2, 'name' => 'bar'],
        ];
        return [
            [
                [
                    $this->createStub('setResultSet', $resultSet1),
                ],
                [
                    $this->createInvocationExpectMethod('setResultSet', $resultSet1),
                ],
            ],
            [
                [
                    $this->createStub('setLastInsertId', 1),
                    $this->createStub('setLastInsertId', 2),
                ],
                [
                    $this->createInvocationExpectMethod('setLastInsertId', 1),
                    $this->createInvocationExpectMethod('setLastInsertId', 2),
                    new RuntimeException('No more items left in stack'),
                ],
            ],
        ];
    }

    private function createStub(string $method, $argument): Stub
    {
        $stub = $this->createMock(Stub::class);
        $stub->expects($this->once())
            ->method('invoke')
            ->willReturnCallback(function (Invocation $invocation) use ($method, $argument) {
                $invocation->$method($argument);
            });
        return $stub;
    }
}
