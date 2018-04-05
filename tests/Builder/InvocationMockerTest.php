<?php
namespace Cz\PHPUnit\MockDB\Builder;

use Cz\PHPUnit\MockDB\Matcher,
    Cz\PHPUnit\MockDB\Matcher\QueryMatcher,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation,
    Cz\PHPUnit\MockDB\Stub,
    Cz\PHPUnit\MockDB\Testcase,
    Cz\PHPUnit\SQL\EqualsSQLQueriesConstraint,
    ArrayObject,
    PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Framework\Constraint\StringStartsWith,
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
     * @dataProvider  provideQuery
     */
    public function testQuery($constraint, $expected)
    {
        $object = $this->createObject();
        $self = $object->query($constraint);
        $this->assertSame($object, $self);
        $queryMatcher = $this->getObjectMatcher($object)
            ->getQueryMatcher();
        $this->assertInstanceOf(QueryMatcher::class, $queryMatcher);
        $actual = $this->getObjectAttribute($queryMatcher, 'constraint');
        $this->assertInstanceOf($expected, $actual);
        if ($constraint instanceof Constraint) {
            $this->assertSame($constraint, $actual);
        }
    }

    public function provideQuery()
    {
        return [
            ['SELECT * FROM `t1`', EqualsSQLQueriesConstraint::class],
            [new EqualsSQLQueriesConstraint('SELECT * FROM `t1`'), EqualsSQLQueriesConstraint::class],
            [$this->stringStartsWith('SELECT'), StringStartsWith::class],
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
            [$this->createMock(Stub::class), Stub::class],
            [new Stub\ReturnResultSetStub([]), Stub\ReturnResultSetStub::class],
            [new Stub\SetAffectedRowsStub(0), Stub\SetAffectedRowsStub::class],
            [new Stub\SetLastInsertIdStub(1), Stub\SetLastInsertIdStub::class],
        ];
    }

    /**
     * @dataProvider  provideWillReturnResultSet
     */
    public function testWillReturnResultSet($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willReturnResultSet(...$arguments);
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
                $this->assertStub($stub, Stub\ReturnResultSetStub::class, 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillReturnResultSetTestCaseConsecutiveCalls(array $values)
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
    public function testWillSetAffectedRows($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willSetAffectedRows(...$arguments);
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
                $this->assertStub($stub, Stub\SetAffectedRowsStub::class, 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillSetAffectedRowsTestCaseConsecutiveCalls(array $values)
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
    public function testWillSetLastInsertId($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willSetLastInsertId(...$arguments);
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
                $this->assertStub($stub, Stub\SetLastInsertIdStub::class, 'value', $value);
                return TRUE;
            },
        ];
    }

    private function createWillSetLastInsertIdTestCaseConsecutiveCalls(array $values)
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
    public function testWillThrowException($arguments, $callback)
    {
        $object = $this->createMockObjectForWillTest($callback);
        $actual = $object->willThrowException(...$arguments);
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
                $this->assertStub($stub, Stub\ThrowExceptionStub::class, 'exception', $value);
                return TRUE;
            },
        ];
    }

    private function createWillThrowExceptionTestCaseConsecutiveCalls(array $values)
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
     * @param  Stub    $stub
     * @param  array   $expectedItems
     * @param  string  $expectedInstanceOf
     * @param  string  $attribute
     */
    private function assertConsecutiveStubs(
        Stub $stub,
        array $expectedItems,
        string $expectedInstanceOf,
        string $attribute
    ) {
        $this->assertInstanceOf(Stub\ConsecutiveCallsStub::class, $stub);
        $stack = $this->getObjectAttribute($stub, 'stack');
        $this->assertInternalType('array', $stack);
        $this->assertCount(count($expectedItems), $stack);
        for ($i = 0; $i < count($expectedItems); $i++) {
            $this->assertStub($stack[$i], $expectedInstanceOf, $attribute, $expectedItems[$i]);
        }
    }

    /**
     * @param  Stub    $stub
     * @param  string  $expectedInstanceOf
     * @param  string  $attribute
     * @param  mixed   $expectedAttribute
     */
    private function assertStub(
        Stub $stub,
        string $expectedInstanceOf,
        string $attribute,
        $expectedAttribute
    ) {
        $this->assertInstanceOf($expectedInstanceOf, $stub);
        $actual = $this->getObjectAttribute($stub, $attribute);
        $this->assertSame($expectedAttribute, $actual);
    }

    /**
     * @param   callable  $checkArgument
     * @return  InvocationMocker
     */
    private function createMockObjectForWillTest(callable $checkArgument)
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
    private function createObject()
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
    private function getObjectMatcher(InvocationMocker $object)
    {
        $matcher = $this->getObjectAttribute($object, 'matcher');
        $this->assertInstanceOf(Matcher::class, $matcher);
        return $matcher;
    }
}
