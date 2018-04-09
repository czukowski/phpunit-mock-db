<?php
namespace Cz\PHPUnit\MockDB\Builder;

use Cz\PHPUnit\MockDB\Matcher,
    Cz\PHPUnit\MockDB\Matcher\QueryMatcher,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Stub,
    Cz\PHPUnit\MockDB\Stub\ConsecutiveCallsStub,
    Cz\PHPUnit\SQL\EqualsSQLQueriesConstraint,
    ArrayObject,
    PHPUnit_Framework_Constraint as Constraint,
    PHPUnit_Framework_Constraint_StringStartsWith as StringStartsWith,
    RuntimeException;

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
    public function testOnConsecutiveCalls($object)
    {
        $actual = $object->onConsecutiveCalls();
        $this->assertInstanceOf('Cz\PHPUnit\MockDB\Builder\ConsecutiveCallsBuilder', $actual);
        $this->assertSame($object, $this->getObjectAttribute($actual, 'builder'));
        $stub = $this->getObjectAttribute($actual, 'stub');
        $this->assertInstanceOf('Cz\PHPUnit\MockDB\Stub\ConsecutiveCallsStub', $stub);
        $this->assertEmpty($this->getObjectAttribute($stub, 'stack'));
    }

    public function provideOnConsecutiveCalls()
    {
        return [
            [$this->createObject()],
        ];
    }

    /**
     * @dataProvider  provideQuery
     */
    public function testQuery($constraint, $expected)
    {
        $object = $this->createObject();
        $self = $object->query($constraint);
        $this->assertSame($object, $self);
        $queryMatcher = $this->getObjectMatcher($object)
            ->getQueryMatcher();
        $this->assertInstanceOf('Cz\PHPUnit\MockDB\Matcher\QueryMatcher', $queryMatcher);
        $actual = $this->getObjectAttribute($queryMatcher, 'constraint');
        $this->assertInstanceOf($expected, $actual);
        if ($constraint instanceof Constraint) {
            $this->assertSame($constraint, $actual);
        }
    }

    public function provideQuery()
    {
        return [
            ['SELECT * FROM `t1`', 'Cz\PHPUnit\SQL\EqualsSQLQueriesConstraint'],
            [new EqualsSQLQueriesConstraint('SELECT * FROM `t1`'), 'Cz\PHPUnit\SQL\EqualsSQLQueriesConstraint'],
            [$this->stringStartsWith('SELECT'), 'PHPUnit_Framework_Constraint_StringStartsWith'],
        ];
    }

    /**
     * @dataProvider  provideWill
     */
    public function testWill($stub, $expected)
    {
        $object = $this->createObject();
        $self = $object->will($stub);
        $this->assertSame($object, $self);
        $actual = $this->getObjectAttribute($this->getObjectMatcher($object), 'stub');
        $this->assertInstanceOf($expected, $actual);
        if ($stub instanceof Stub) {
            $this->assertSame($stub, $actual);
        }
    }

    public function provideWill()
    {
        return [
            [$this->createMock('Cz\PHPUnit\MockDB\Stub'), 'Cz\PHPUnit\MockDB\Stub'],
            [new Stub\ReturnResultSetStub([]), 'Cz\PHPUnit\MockDB\Stub\ReturnResultSetStub'],
            [new Stub\SetAffectedRowsStub(0), 'Cz\PHPUnit\MockDB\Stub\SetAffectedRowsStub'],
            [new Stub\SetLastInsertIdStub(1), 'Cz\PHPUnit\MockDB\Stub\SetLastInsertIdStub'],
        ];
    }

    /**
     * @dataProvider  provideWillInvokeCallback
     */
    public function testWillInvokeCallback($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = call_user_func_array([$object, 'willInvokeCallback'], $arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillInvokeCallback()
    {
        return [
            $this->createWillInvokeCallbackTestCaseSingleCall(function () {}),
            $this->createWillInvokeCallbackTestCaseConsecutiveCalls([function () {}, function () {}]),
        ];
    }

    private function createWillInvokeCallbackTestCaseSingleCall($callback)
    {
        return [
            [$callback],
            function ($stub) use ($callback) {
                $this->assertStub($stub, 'Cz\PHPUnit\MockDB\Stub\InvokeCallbackStub', 'callback', $callback);
                return TRUE;
            },
        ];
    }

    private function createWillInvokeCallbackTestCaseConsecutiveCalls($callbacks)
    {
        return [
            $callbacks,
            function ($stub) use ($callbacks) {
                $this->assertConsecutiveStubs($stub, $callbacks, 'Cz\PHPUnit\MockDB\Stub\InvokeCallbackStub', 'callback');
                return TRUE;
            },
        ];
    }

    /**
     * @dataProvider  provideWillReturnResultSet
     */
    public function testWillReturnResultSet($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = call_user_func_array([$object, 'willReturnResultSet'], $arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillReturnResultSet()
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

    private function createWillReturnResultSetTestCaseSingleCall($value)
    {
        return [
            [$value],
            function ($stub) use ($value) {
                $this->assertStub($stub, 'Cz\PHPUnit\MockDB\Stub\ReturnResultSetStub', 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillReturnResultSetTestCaseConsecutiveCalls(array $values)
    {
        return [
            $values,
            function ($stub) use ($values) {
                $this->assertConsecutiveStubs($stub, $values, 'Cz\PHPUnit\MockDB\Stub\ReturnResultSetStub', 'value');
                return TRUE;
            }
        ];
    }

    /**
     * @dataProvider  provideWillSetAffectedRows
     */
    public function testWillSetAffectedRows($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = call_user_func_array([$object, 'willSetAffectedRows'], $arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillSetAffectedRows()
    {
        return [
            $this->createWillSetAffectedRowsTestCaseSingleCall(0),
            $this->createWillSetAffectedRowsTestCaseSingleCall(100),
            $this->createWillSetAffectedRowsTestCaseConsecutiveCalls([1, 2, 3]),
        ];
    }

    private function createWillSetAffectedRowsTestCaseSingleCall($value)
    {
        return [
            [$value],
            function ($stub) use ($value) {
                $this->assertStub($stub, 'Cz\PHPUnit\MockDB\Stub\SetAffectedRowsStub', 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillSetAffectedRowsTestCaseConsecutiveCalls(array $values)
    {
        return [
            $values,
            function ($stub) use ($values) {
                $this->assertConsecutiveStubs($stub, $values, 'Cz\PHPUnit\MockDB\Stub\SetAffectedRowsStub', 'value');
                return TRUE;
            }
        ];
    }

    /**
     * @dataProvider  provideWillSetLastInsertId
     */
    public function testWillSetLastInsertId($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = call_user_func_array([$object, 'willSetLastInsertId'], $arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillSetLastInsertId()
    {
        return [
            $this->createWillSetLastInsertIdTestCaseSingleCall(NULL),
            $this->createWillSetLastInsertIdTestCaseSingleCall(123),
            $this->createWillSetLastInsertIdTestCaseSingleCall('456'),
            $this->createWillSetLastInsertIdTestCaseConsecutiveCalls([NULL, 1, 2]),
        ];
    }

    private function createWillSetLastInsertIdTestCaseSingleCall($value)
    {
        return [
            [$value],
            function ($stub) use ($value) {
                $this->assertStub($stub, 'Cz\PHPUnit\MockDB\Stub\SetLastInsertIdStub', 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillSetLastInsertIdTestCaseConsecutiveCalls(array $values)
    {
        return [
            $values,
            function ($stub) use ($values) {
                $this->assertConsecutiveStubs($stub, $values, 'Cz\PHPUnit\MockDB\Stub\SetLastInsertIdStub', 'value');
                return TRUE;
            }
        ];
    }

    /**
     * @dataProvider  provideWillThrowException
     */
    public function testWillThrowException($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = call_user_func_array([$object, 'willThrowException'], $arguments);
        $this->assertSame($object, $actual);
    }

    public function provideWillThrowException()
    {
        return [
            $this->createWillThrowExceptionTestCaseSingleCall(new RuntimeException),
            $this->createWillThrowExceptionTestCaseConsecutiveCalls([new RuntimeException, new RuntimeException]),
        ];
    }

    private function createWillThrowExceptionTestCaseSingleCall($value)
    {
        return [
            [$value],
            function ($stub) use ($value) {
                $this->assertStub($stub, 'Cz\PHPUnit\MockDB\Stub\ThrowExceptionStub', 'exception', $value);
                return TRUE;
            },
        ];
    }

    private function createWillThrowExceptionTestCaseConsecutiveCalls(array $values)
    {
        return [
            $values,
            function ($stub) use ($values) {
                $this->assertConsecutiveStubs($stub, $values, 'Cz\PHPUnit\MockDB\Stub\ThrowExceptionStub', 'exception');
                return TRUE;
            }
        ];
    }

    /**
     * @param   callable  $checkArgument
     * @return  InvocationMocker
     */
    private function createMockObjectForWillTest(callable $checkArgument)
    {
        $object = $this->getMockBuilder('Cz\PHPUnit\MockDB\Builder\InvocationMocker')
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
    private function createObject()
    {
        return new InvocationMocker(
            $this->createMock('Cz\PHPUnit\MockDB\Stub\MatcherCollection'),
            $this->createMock('Cz\PHPUnit\MockDB\Matcher\RecordedInvocation')
        );
    }

    /**
     * @param   InvocationMocker  $object
     * @return  Matcher
     */
    private function getObjectMatcher(InvocationMocker $object)
    {
        $matcher = $this->getObjectAttribute($object, 'matcher');
        $this->assertInstanceOf('Cz\PHPUnit\MockDB\Matcher', $matcher);
        return $matcher;
    }
}
