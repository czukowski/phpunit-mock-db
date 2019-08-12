<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Invocation,
    LogicException,
    PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Framework\Exception as FrameworkException,
    RuntimeException,
    Throwable;

/**
 * MockTraitIntegrationTest
 * 
 * Only "happy" test cases from `MockIntegrationTest` are here.
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MockTraitIntegrationTest extends Testcase
{
    use MockTrait;

    /**
     * @var  DatabaseDriverInterface
     */
    private $db;

    /**
     * Expect any query producing a result set, zero or more times. Invoke a query once.
     * 
     * @dataProvider  provideMatchSingleSelectInvocation
     */
    public function testMatchAnyQueryAnyInvocationCount(string $query, array $expected): void
    {
        $this->createDatabaseMock()
            ->expects($this->any())
            ->willReturnResultSet($expected);
        $actual = $this->db->query($query);
        $this->assertSame($expected, $actual);
    }

    /**
     * Expect any single query producing a result set. Invoke once.
     * 
     * @dataProvider  provideMatchSingleSelectInvocation
     */
    public function testMatchAnyQuerySingleInvocation(string $query, array $expected): void
    {
        $this->createDatabaseMock()
            ->expects($this->once())
            ->willReturnResultSet($expected);
        $actual = $this->db->query($query);
        $this->assertSame($expected, $actual);
    }

    /**
     * Expect any query producing a result set, zero or more times. Do not invoke any.
     * 
     * @dataProvider  provideMatchSingleSelectInvocation
     */
    public function testMatchAnyQueryAnyInvocationCountNoneInvoked($_, array $expected): void
    {
        $this->createDatabaseMock()
            ->expects($this->any())
            ->willReturnResultSet($expected);
    }

    public function provideMatchSingleSelectInvocation(): array
    {
        return [
            [
                'SELECT * FROM `t`',
                [['foo' => 'bar']],
            ],
        ];
    }

    /**
     * Expect two different queries producing result sets, each executed once, regardless of the order.
     * Invoke both once, starting with the 2nd query.
     * 
     * @dataProvider  provideMatchTwoSelectInvocations
     */
    public function testMatchWithQueryMatchersOnceEach(
        string $query1,
        array $expected1,
        string $query2,
        array $expected2
    ): void
    {
        $mock = $this->createDatabaseMock();
        $mock->expects($this->once())
            ->query($query1)
            ->willReturnResultSet($expected1);
        $mock->expects($this->once())
            ->query($query2)
            ->willReturnResultSet($expected2);

        // Invoke in reverse order.
        $actual2 = $this->db->query($query2);
        $this->assertSame($expected2, $actual2);
        $actual1 = $this->db->query($query1);
        $this->assertSame($expected1, $actual1);
    }

    public function provideMatchTwoSelectInvocations(): array
    {
        return [
            [
                'SELECT * FROM `t1`',
                [['foo' => 'bar']],
                'SELECT * FROM `t2`',
                [['no' => 'way']],
            ],
        ];
    }

    /**
     * Expect two queries producing insert IDs executed as 2nd and 3rd queries, and also
     * a query producing a result set executed once at any position. Invoke select query,
     * then both insert queries in the correct order.
     * 
     * @dataProvider  provideMatchMixedQueriesWithQueryMatchersOnceEach
     */
    public function testMatchMixedQueriesWithQueryMatchersOnceEach(
        string $query1,
        array $expected1,
        string $query2,
        int $expected2,
        string $query3,
        int $expected3
    ): void
    {
        $mock = $this->createDatabaseMock();
        $mock->expects($this->at(1))
            ->query($query2)
            ->willSetLastInsertId($expected2);
        $mock->expects($this->at(2))
            ->query($query3)
            ->willSetLastInsertId($expected3);
        $mock->expects($this->once())
            ->query($query1)
            ->willReturnResultSet($expected1);

        $actual1 = $this->db->query($query1);
        $this->assertSame($expected1, $actual1);
        $actual2 = $this->db->query($query2);
        $this->assertSame($expected2, $actual2);
        $actual3 = $this->db->query($query3);
        $this->assertSame($expected3, $actual3);
    }

    public function provideMatchMixedQueriesWithQueryMatchersOnceEach(): array
    {
        return [
            [
                'SELECT * FROM `t1`',
                [['foo' => 'bar']],
                'INSERT INTO `t1` VALUES (1, 2, 3)',
                1,
                'INSERT INTO `t1` VALUES (1, 2, 3)',
                2,
            ],
        ];
    }

    /**
     * Expect a series of consequent queries producing insert IDs. Invoke all of then in order.
     * 
     * @dataProvider  provideMatchWithQueryMatchersWithConsecutiveCalls
     */
    public function testMatchWithQueryMatchersWithConsecutiveCalls(string $query, array $expecteds): void
    {
        $this->createDatabaseMock()
            ->expects($this->exactly(count($expecteds)))
            ->query($query)
            ->willSetLastInsertId(...$expecteds);

        foreach ($expecteds as $expected) {
            $actual = $this->db->query($query);
            $this->assertSame($expected, $actual);
        }
    }

    public function provideMatchWithQueryMatchersWithConsecutiveCalls(): array
    {
        return [
            [
                'INSERT INTO `t1` VALUES ("a", "b", "c")',
                [1, 2, 3],
            ],
        ];
    }

    /**
     * Expect a query producing a number of affected rows to be executed repeatedly
     * exactly specified amount of times. Invoke as specified.
     * 
     * @dataProvider  provideMatchWithQueryMatcherAssertAffectedRows
     */
    public function testMatchWithQueryMatcherAssertAffectedRows(
        int $expectTimes,
        string $query,
        int $expected
    ): void
    {
        $this->createDatabaseMock()
            ->expects($this->exactly($expectTimes))
            ->query($query)
            ->willSetAffectedRows($expected);

        for ($i = 0; $i < $expectTimes; $i++) {
            $actual = $this->db->query($query);
            $this->assertSame($expected, $actual);
        }
    }

    public function provideMatchWithQueryMatcherAssertAffectedRows(): array
    {
        return [
            [
                2,
                'UPDATE `t1` SET `foo` = "bar"',
                1,
            ],
        ];
    }

    /**
     * Expect a query producing a result set executed once, while being validated against
     * a specified native PHPUnit constraint. Invoke passed query once.
     * 
     * @dataProvider  provideMatchQueryWithPHPUnitConstraint
     */
    public function testMatchQueryWithPHPUnitConstraint(
        Constraint $constraint,
        string $query,
        array $expected
    ): void
    {
        $this->createDatabaseMock()
            ->expects($this->once())
            ->query($constraint)
            ->willReturnResultSet($expected);
        $actual = $this->db->query($query);
        $this->assertSame($expected, $actual);
    }

    public function provideMatchQueryWithPHPUnitConstraint(): array
    {
        return [
            [
                $this->stringStartsWith('SELECT'),
                'SELECT * FROM `t`',
                [['foo' => 'bar']],
            ],
        ];
    }

    /**
     * Expect a query producing a series of consequent queries producing insert IDs using consecutive calls
     * builder in a way that the 1st and 2nd invocations succeed, 3rd invocation throws an exception and the
     * 4th invocation succeed again. Invoke a passed query four times and check the correct result each time.
     * 
     * @dataProvider  provideMatchWithQueryMatchersWithConsecutiveCallsBuilder
     */
    public function testMatchWithQueryMatchersWithConsecutiveCallsBuilder(
        string $query,
        Throwable $exception,
        array $expecteds
    ): void
    {
        $queue = $expecteds;
        $this->createDatabaseMock()
            ->expects($this->atLeast(count($expecteds) + 1))
            ->query($query)
            ->onConsecutiveCalls()
            ->willSetLastInsertId(array_shift($queue))
            ->willSetLastInsertId(array_shift($queue))
            ->willThrowException($exception)
            ->willSetLastInsertId(array_shift($queue));

        $actual0 = $this->db->query($query);
        $this->assertSame($expecteds[0], $actual0);

        $actual1 = $this->db->query($query);
        $this->assertSame($expecteds[1], $actual1);

        try {
            $this->db->query($query);
            $this->fail('Expected exception');
        }
        catch (Throwable $e) {
            if ($e instanceof FrameworkException) {
                throw $e;
            }
            $this->assertInstanceOf(get_class($exception), $e);
        }

        $actual2 = $this->db->query($query);
        $this->assertSame($expecteds[2], $actual2);
    }

    public function provideMatchWithQueryMatchersWithConsecutiveCallsBuilder(): array
    {
        return [
            [
                'INSERT INTO `t1` VALUES ("a", "b", "c")',
                new RuntimeException('Deadlock'),
                [1, 2, 3],
            ],
        ];
    }

    /**
     * Expect a single query and set up passed callback functions to create query result.
     * Invoke once and check for the correct result.
     * 
     * @dataProvider  provideMatchWithQueryMatchersWithCustomCallbackHandlers
     */
    public function testMatchWithQueryMatchersWithCustomCallbackHandlers(
        Constraint $constraint,
        callable $callback,
        string $query,
        $expected
    ): void
    {
        $this->createDatabaseMock()
            ->expects($this->once())
            ->query($constraint)
            ->willInvokeCallback($callback);

        $actual = $this->db->query($query);
        $this->assertSame($expected, $actual);
    }

    public function provideMatchWithQueryMatchersWithCustomCallbackHandlers(): array
    {
        return [
            [
                $this->stringStartsWith('INSERT'),
                function (Invocation $invocation) {
                    $invocation->setLastInsertId(1);
                },
                'INSERT INTO `t1` VALUES ("foo")',
                1,
            ],
            [
                $this->stringStartsWith('UPDATE'),
                function (Invocation $invocation) {
                    $invocation->setAffectedRows(0);
                },
                'UPDATE `t1` SET `name` = "foo" WHERE `name` = "bar"',
                0,
            ],
            [
                $this->stringStartsWith('SELECT'),
                function (Invocation $invocation) {
                    $invocation->setResultSet([['name' => 'foo']]);
                },
                'SELECT * FROM `t`',
                [['name' => 'foo']],
            ],
        ];
    }

    protected function getDatabaseDriver(): DatabaseDriverInterface
    {
        return $this->db;
    }

    protected function setUp(): void
    {
        $this->db = new TestingDatabaseDriver;
    }

    protected function tearDown(): void
    {
        $this->db = NULL;
    }
}
