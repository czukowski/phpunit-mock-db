<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Builder;

use Cz\PHPUnit\MockDB\Matcher,
    Cz\PHPUnit\MockDB\Matcher\AnyParameters,
    Cz\PHPUnit\MockDB\Matcher\ParametersMatch,
    Cz\PHPUnit\MockDB\Matcher\QueryMatcher,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Stub,
    Cz\PHPUnit\MockDB\Stub\ConsecutiveCallsStub,
    Cz\PHPUnit\SQL\EqualsSQLQueriesConstraint,
    ArrayObject,
    PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Framework\Constraint\StringStartsWith,
    RuntimeException,
    Throwable;

/**
 * InvocationMockerTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvocationMockerTest extends Testcase
{
    /**
     * @dataProvider  provideOnConsecutiveCalls
     */
    public function testOnConsecutiveCalls(InvocationMocker $object): void
    {
        $actual = $object->onConsecutiveCalls();
        $this->assertInstanceOf(ConsecutiveCallsBuilder::class, $actual);
        $this->assertSame($object, $this->getObjectPropertyValue($actual, 'builder'));
        $stub = $this->getObjectPropertyValue($actual, 'stub');
        $this->assertInstanceOf(ConsecutiveCallsStub::class, $stub);
        $this->assertEmpty($this->getObjectPropertyValue($stub, 'stack'));
    }

    public function provideOnConsecutiveCalls(): array
    {
        return [
            [$this->createObject()],
        ];
    }

    /**
     * @dataProvider  provideQuery
     */
    public function testQuery($constraint, string $expected): void
    {
        $object = $this->createObject();
        $self = $object->query($constraint);
        $this->assertSame($object, $self);
        $queryMatcher = $this->getObjectMatcher($object)
            ->getQueryMatcher();
        $this->assertInstanceOf(QueryMatcher::class, $queryMatcher);
        $actual = $this->getObjectPropertyValue($queryMatcher, 'constraint');
        $this->assertInstanceOf($expected, $actual);
        if ($constraint instanceof Constraint) {
            $this->assertSame($constraint, $actual);
        }
    }

    public function provideQuery(): array
    {
        return [
            ['SELECT * FROM `t1`', EqualsSQLQueriesConstraint::class],
            [new EqualsSQLQueriesConstraint('SELECT * FROM `t1`'), EqualsSQLQueriesConstraint::class],
            [$this->stringStartsWith('SELECT'), StringStartsWith::class],
        ];
    }

    /**
     * @dataProvider  provideWith
     */
    public function testWith(array $parameters, array $expected): void
    {
        $object = $this->createObject();
        $self = $object->with($parameters);
        $this->assertSame($object, $self);
        $parametersMatch = $this->getObjectMatcher($object)
            ->getParametersMatcher();
        $this->assertInstanceOf(ParametersMatch::class, $parametersMatch);
        $actual = $this->getObjectPropertyValue($parametersMatch, 'parameters');
        $this->assertCount(count($expected), $actual);
        for ($i = 0; $i < count($expected); $i++) {
            if ($expected[$i] instanceof Constraint) {
                $this->assertSame($expected[$i], $actual[$i]);
            }
            else {
                $this->assertInstanceOf(Constraint::class, $actual[$i]);
                $actual[$i]->evaluate($expected[$i]);
            }
        }
    }

    public function provideWith(): array
    {
        $anyMatcher = $this->anything();
        return [
            [[1, 2], [1, 2]],
            [[3.14, $anyMatcher], [3.14, $anyMatcher]],
        ];
    }

    public function testWithAnyParameters(): void
    {
        $object = $this->createObject();
        $self = $object->withAnyParameters();
        $this->assertSame($object, $self);
        $parametersMatch = $this->getObjectMatcher($object)
            ->getParametersMatcher();
        $this->assertInstanceOf(AnyParameters::class, $parametersMatch);
    }

    /**
     * @dataProvider  provideWill
     */
    public function testWill(Stub $stub, string $expected): void
    {
        $object = $this->createObject();
        $self = $object->will($stub);
        $this->assertSame($object, $self);
        $actual = $this->getObjectPropertyValue($this->getObjectMatcher($object), 'stub');
        $this->assertInstanceOf($expected, $actual);
        if ($stub instanceof Stub) {
            $this->assertSame($stub, $actual);
        }
    }

    public function provideWill(): array
    {
        return [
            [$this->createMock(Stub::class), Stub::class],
            [new Stub\ReturnResultSetStub([]), Stub\ReturnResultSetStub::class],
            [new Stub\SetAffectedRowsStub(0), Stub\SetAffectedRowsStub::class],
            [new Stub\SetLastInsertIdStub(1), Stub\SetLastInsertIdStub::class],
        ];
    }

    /**
     * @dataProvider  provideWillInvokeCallback
     */
    public function testWillInvokeCallback(array $arguments, callable $callback): void
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willInvokeCallback(...$arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillInvokeCallback(): array
    {
        return [
            $this->createWillInvokeCallbackTestCaseSingleCall(function () {}),
            $this->createWillInvokeCallbackTestCaseConsecutiveCalls([function () {}, function () {}]),
        ];
    }

    private function createWillInvokeCallbackTestCaseSingleCall(callable $callback): array
    {
        return [
            [$callback],
            function ($stub) use ($callback) {
                $this->assertStub($stub, Stub\InvokeCallbackStub::class, 'callback', $callback);
                return TRUE;
            },
        ];
    }

    private function createWillInvokeCallbackTestCaseConsecutiveCalls(array $callbacks): array
    {
        return [
            $callbacks,
            function ($stub) use ($callbacks) {
                $this->assertConsecutiveStubs($stub, $callbacks, Stub\InvokeCallbackStub::class, 'callback');
                return TRUE;
            },
        ];
    }

    /**
     * @dataProvider  provideWillReturnResultSet
     */
    public function testWillReturnResultSet(array $arguments, callable $callback): void
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willReturnResultSet(...$arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillReturnResultSet(): array
    {
        $resultSet1 = [];
        $resultSet2 = [
            ['id' => 1],
            ['id' => 2],
        ];
        $resultSet3 = [
            ['id' => 2],
            ['id' => 3],
        ];
        return [
            $this->createWillReturnResultSetTestCaseSingleCall(NULL),
            $this->createWillReturnResultSetTestCaseSingleCall($resultSet1),
            $this->createWillReturnResultSetTestCaseSingleCall($resultSet2),
            $this->createWillReturnResultSetTestCaseSingleCall(new ArrayObject($resultSet2)),
            $this->createWillReturnResultSetTestCaseConsecutiveCalls([$resultSet1, $resultSet2]),
            $this->createWillReturnResultSetTestCaseConsecutiveCalls([new ArrayObject($resultSet2), new ArrayObject($resultSet3)]),
        ];
    }

    private function createWillReturnResultSetTestCaseSingleCall(?iterable $value): array
    {
        return [
            [$value],
            function ($stub) use ($value) {
                $this->assertStub($stub, Stub\ReturnResultSetStub::class, 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillReturnResultSetTestCaseConsecutiveCalls(array $values): array
    {
        return [
            $values,
            function ($stub) use ($values) {
                $this->assertConsecutiveStubs($stub, $values, Stub\ReturnResultSetStub::class, 'value');
                return TRUE;
            }
        ];
    }

    /**
     * @dataProvider  provideWillSetAffectedRows
     */
    public function testWillSetAffectedRows(array $arguments, callable $callback): void
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willSetAffectedRows(...$arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillSetAffectedRows(): array
    {
        return [
            $this->createWillSetAffectedRowsTestCaseSingleCall(0),
            $this->createWillSetAffectedRowsTestCaseSingleCall(100),
            $this->createWillSetAffectedRowsTestCaseConsecutiveCalls([1, 2, 3]),
        ];
    }

    private function createWillSetAffectedRowsTestCaseSingleCall(int $value): array
    {
        return [
            [$value],
            function ($stub) use ($value) {
                $this->assertStub($stub, Stub\SetAffectedRowsStub::class, 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillSetAffectedRowsTestCaseConsecutiveCalls(array $values): array
    {
        return [
            $values,
            function ($stub) use ($values) {
                $this->assertConsecutiveStubs($stub, $values, Stub\SetAffectedRowsStub::class, 'value');
                return TRUE;
            }
        ];
    }

    /**
     * @dataProvider  provideWillSetLastInsertId
     */
    public function testWillSetLastInsertId(array $arguments, callable $callback): void
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willSetLastInsertId(...$arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillSetLastInsertId(): array
    {
        return [
            $this->createWillSetLastInsertIdTestCaseSingleCall(NULL),
            $this->createWillSetLastInsertIdTestCaseSingleCall(123),
            $this->createWillSetLastInsertIdTestCaseSingleCall('456'),
            $this->createWillSetLastInsertIdTestCaseConsecutiveCalls([NULL, 1, 2]),
        ];
    }

    private function createWillSetLastInsertIdTestCaseSingleCall($value): array
    {
        return [
            [$value],
            function ($stub) use ($value) {
                $this->assertStub($stub, Stub\SetLastInsertIdStub::class, 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillSetLastInsertIdTestCaseConsecutiveCalls(array $values): array
    {
        return [
            $values,
            function ($stub) use ($values) {
                $this->assertConsecutiveStubs($stub, $values, Stub\SetLastInsertIdStub::class, 'value');
                return TRUE;
            }
        ];
    }

    /**
     * @dataProvider  provideWillThrowException
     */
    public function testWillThrowException(array $arguments, callable $callback): void
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willThrowException(...$arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillThrowException(): array
    {
        return [
            $this->createWillThrowExceptionTestCaseSingleCall(new RuntimeException),
            $this->createWillThrowExceptionTestCaseConsecutiveCalls([new RuntimeException, new RuntimeException]),
        ];
    }

    private function createWillThrowExceptionTestCaseSingleCall(Throwable $value): array
    {
        return [
            [$value],
            function ($stub) use ($value) {
                $this->assertStub($stub, Stub\ThrowExceptionStub::class, 'exception', $value);
                return TRUE;
            },
        ];
    }

    private function createWillThrowExceptionTestCaseConsecutiveCalls(array $values): array
    {
        return [
            $values,
            function ($stub) use ($values) {
                $this->assertConsecutiveStubs($stub, $values, Stub\ThrowExceptionStub::class, 'exception');
                return TRUE;
            }
        ];
    }

    /**
     * @param   callable  $checkArgument
     * @return  InvocationMocker
     */
    private function createMockObjectForWillTest(callable $checkArgument): InvocationMocker
    {
        $object = $this->getMockBuilder(InvocationMocker::class)
            ->disableOriginalConstructor()
            ->setMethods(['will'])
            ->getMock();
        $object->expects($this->once())
            ->method('will')
            ->with($this->callback($checkArgument))
            ->willReturn($object);
        return $object;
    }

    /**
     * @return  InvocationMocker
     */
    private function createObject(): InvocationMocker
    {
        return new InvocationMocker(
            $this->createMock(Stub\MatcherCollection::class),
            $this->createMock(RecordedInvocation::class)
        );
    }

    /**
     * @param   InvocationMocker  $object
     * @return  Matcher
     */
    private function getObjectMatcher(InvocationMocker $object): Matcher
    {
        $matcher = $this->getObjectPropertyValue($object, 'matcher');
        $this->assertInstanceOf(Matcher::class, $matcher);
        return $matcher;
    }
}
