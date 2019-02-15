<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Builder;

use Cz\PHPUnit\MockDB\Stub,
    Cz\PHPUnit\MockDB\Stub\ConsecutiveCallsStub,
    Cz\PHPUnit\MockDB\Stub\InvokeCallbackStub,
    Cz\PHPUnit\MockDB\Stub\ReturnResultSetStub,
    Cz\PHPUnit\MockDB\Stub\SetAffectedRowsStub,
    Cz\PHPUnit\MockDB\Stub\SetLastInsertIdStub,
    Cz\PHPUnit\MockDB\Stub\ThrowExceptionStub,
    ArrayObject,
    RuntimeException,
    Throwable;

/**
 * ConsecutiveCallsBuilderTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ConsecutiveCallsBuilderTest extends Testcase
{
    /**
     * @dataProvider  provideDone
     */
    public function testDone(InvocationMocker $builder): void
    {
        $object = new ConsecutiveCallsBuilder(
            $builder,
            $this->createMock(ConsecutiveCallsStub::class)
        );
        $actual = $object->done();
        $this->assertSame($builder, $actual);
    }

    public function provideDone(): array
    {
        return [
            [$this->createMock(InvocationMocker::class)],
        ];
    }

    /**
     * @dataProvider  provideWill
     */
    public function testWill(Stub $argument): void
    {
        $stub = $this->createMock(ConsecutiveCallsStub::class);
        $stub->expects($this->once())
            ->method('addStub')
            ->with($argument);
        $object = new ConsecutiveCallsBuilder(
            $this->createMock(InvocationMocker::class),
            $stub
        );
        $actual = $object->will($argument);
        $this->assertSame($object, $actual);
    }

    public function provideWill(): array
    {
        return [
            [$this->createMock(Stub::class)],
            [new ReturnResultSetStub([])],
            [new SetAffectedRowsStub(0)],
            [new SetLastInsertIdStub(1)],
            [new ThrowExceptionStub(new RuntimeException)],
        ];
    }

    /**
     * @dataProvider  provideWillInvokeCallback
     */
    public function testWillInvokeCallback(callable $callback): void
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($callback) {
            $this->assertStub($stub, InvokeCallbackStub::class, 'callback', $callback);
            return TRUE;
        });
        $actual = $object->willInvokeCallback($callback);
        $this->assertSame($object, $actual);
    }

    public function provideWillInvokeCallback(): array
    {
        return [
            [function () {}],
        ];
    }

    /**
     * @dataProvider  provideWillReturnResultSet
     */
    public function testWillReturnResultSet(iterable $resultSet): void
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($resultSet) {
            $this->assertStub($stub, ReturnResultSetStub::class, 'value', $resultSet);
            return TRUE;
        });
        $actual = $object->willReturnResultSet($resultSet);
        $this->assertSame($object, $actual);
    }

    public function provideWillReturnResultSet(): array
    {
        $resultSet1 = [];
        $resultSet2 = [
            ['id' => 1],
            ['id' => 2],
        ];
        return [
            [$resultSet1],
            [new ArrayObject($resultSet2)],
        ];
    }

    /**
     * @dataProvider  provideWillSetAffectedRows
     */
    public function testWillSetAffectedRows(?int $count): void
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($count) {
            $this->assertStub($stub, SetAffectedRowsStub::class, 'value', $count);
            return TRUE;
        });
        $actual = $object->willSetAffectedRows($count);
        $this->assertSame($object, $actual);
    }

    public function provideWillSetAffectedRows(): array
    {
        return [
            [NULL],
            [0],
            [10],
        ];
    }

    /**
     * @dataProvider  provideWillSetLastInsertId
     */
    public function testWillSetLastInsertId($value): void
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($value) {
            $this->assertStub($stub, SetLastInsertIdStub::class, 'value', $value);
            return TRUE;
        });
        $actual = $object->willSetLastInsertId($value);
        $this->assertSame($object, $actual);
    }

    public function provideWillSetLastInsertId(): array
    {
        return [
            [NULL],
            [1],
            ['2'],
        ];
    }

    /**
     * @dataProvider  provideWillThrowException
     */
    public function testWillThrowException(Throwable $value): void
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($value) {
            $this->assertStub($stub, ThrowExceptionStub::class, 'exception', $value);
            return TRUE;
        });
        $actual = $object->willThrowException($value);
        $this->assertSame($object, $actual);
    }

    public function provideWillThrowException(): array
    {
        return [
            [new RuntimeException],
        ];
    }

    /**
     * @param   callable  $checkArgument
     * @return  ConsecutiveCallsBuilder
     */
    private function createMockObjectForWillTest(callable $checkArgument): ConsecutiveCallsBuilder
    {
        $object = $this->getMockBuilder(ConsecutiveCallsBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['will'])
            ->getMock();
        $object->expects($this->once())
            ->method('will')
            ->with($this->callback($checkArgument))
            ->willReturn($object);
        return $object;
    }
}
