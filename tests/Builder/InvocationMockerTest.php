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
    public function testWillReturnResultSet($resultSet)
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($resultSet) {
            $this->assertInstanceOf(Stub\ReturnResultSetStub::class, $stub);
            $actual = $this->getObjectAttribute($stub, 'value');
            $this->assertSame($resultSet, $actual);
            return TRUE;
        });
        $actual = $object->willReturnResultSet($resultSet);
        $this->assertSame($object, $actual);
    }

    public function provideWillReturnResultSet()
    {
        return [
            [
                NULL,
            ],
            [
                []
            ],
            [
                [
                    ['id' => 1],
                    ['id' => 2],
                ],
            ],
            [
                new ArrayObject([
                    ['id' => 1],
                    ['id' => 2],
                ]),
            ],
        ];
    }

    /**
     * @dataProvider  provideWillSetAffectedRows
     */
    public function testWillSetAffectedRows($count)
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($count) {
            $this->assertInstanceOf(Stub\SetAffectedRowsStub::class, $stub);
            $actual = $this->getObjectAttribute($stub, 'value');
            $this->assertSame($count, $actual);
            return TRUE;
        });
        $actual = $object->willSetAffectedRows($count);
        $this->assertSame($object, $actual);
    }

    public function provideWillSetAffectedRows()
    {
        return [
            [0],
            [100],
        ];
    }

    /**
     * @dataProvider  provideWillSetLastInsertId
     */
    public function testWillSetLastInsertId($value)
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($value) {
            $this->assertInstanceOf(Stub\SetLastInsertIdStub::class, $stub);
            $actual = $this->getObjectAttribute($stub, 'value');
            $this->assertSame($value, $actual);
            return TRUE;
        });
        $actual = $object->willSetLastInsertId($value);
        $this->assertSame($object, $actual);
    }

    public function provideWillSetLastInsertId()
    {
        return [
            [NULL],
            [123],
            ['456'],
        ];
    }

    /**
     * @dataProvider  provideWillThrowException
     */
    public function testWillThrowException($error)
    {
        $object = $this->createMockObjectForWillTest(function ($stub) use ($error) {
            $this->assertInstanceOf(Stub\ThrowExceptionStub::class, $stub);
            $actual = $this->getObjectAttribute($stub, 'exception');
            $this->assertSame($error, $actual);
            return TRUE;
        });
        $actual = $object->willThrowException($error);
        $this->assertSame($object, $actual);
    }

    public function provideWillThrowException()
    {
        return [
            [new RuntimeException],
        ];
    }

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

    private function createObject()
    {
        return new InvocationMocker(
            $this->createMock(Stub\MatcherCollection::class),
            $this->createMock(RecordedInvocation::class)
        );
    }

    private function getObjectMatcher(InvocationMocker $object)
    {
        $matcher = $this->getObjectAttribute($object, 'matcher');
        $this->assertInstanceOf(Matcher::class, $matcher);
        return $matcher;
    }
}
