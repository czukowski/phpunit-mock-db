<?php
namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Invocation,
    LogicException,
    PHPUnit\Framework\Exception,
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
     * @dataProvider  provideMatchSingleSelectInvocation
     */
    public function testMatchAnyQueryAnyInvocationCount($query, $expected)
    {
        $this->createDatabaseMock()
            ->expects($this->any())
            ->willReturnResultSet($expected);
        $actual = $this->db->query($query);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider  provideMatchSingleSelectInvocation
     */
    public function testMatchAnyQuerySingleInvocation($query, $expected)
    {
        $this->createDatabaseMock()
            ->expects($this->once())
            ->willReturnResultSet($expected);
        $actual = $this->db->query($query);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider  provideMatchSingleSelectInvocation
     */
    public function testMatchAnyQueryAnyInvocationCountNoneInvoked($_, $expected)
    {
        $this->createDatabaseMock()
            ->expects($this->any())
            ->willReturnResultSet($expected);
    }

    public function provideMatchSingleSelectInvocation()
    {
        return [
            [
                'SELECT * FROM `t`',
                [['foo' => 'bar']],
            ],
        ];
    }

    /**
     * @dataProvider  provideMatchTwoSelectInvocations
     */
    public function testMatchWithQueryMatchersOnceEach($query1, $expected1, $query2, $expected2)
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

    public function provideMatchTwoSelectInvocations()
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
     * @dataProvider  provideMatchMixedQueriesWithQueryMatchersOnceEach
     */
    public function testMatchMixedQueriesWithQueryMatchersOnceEach($query1, $expected1, $query2, $expected2, $query3, $expected3)
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

    public function provideMatchMixedQueriesWithQueryMatchersOnceEach()
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
     * @dataProvider  provideMatchWithQueryMatchersWithConsecutiveCalls
     */
    public function testMatchWithQueryMatchersWithConsecutiveCalls($query, $expecteds)
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

    public function provideMatchWithQueryMatchersWithConsecutiveCalls()
    {
        return [
            [
                'INSERT INTO `t1` VALUES ("a", "b", "c")',
                [1, 2, 3],
            ],
        ];
    }

    /**
     * @dataProvider  provideMatchWithQueryMatcherAssertAffectedRows
     */
    public function testMatchWithQueryMatcherAssertAffectedRows($expectTimes, $query, $expected)
    {
        $this->createDatabaseMock()
            ->expects($this->exactly($expectTimes))
            ->query($query)
            ->willSetAffectedRows($expected);
        $actual1 = $this->db->query($query);
        $this->assertSame($expected, $actual1);
        $actual2 = $this->db->query($query);
        $this->assertSame($expected, $actual2);
    }

    public function provideMatchWithQueryMatcherAssertAffectedRows()
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
     * @dataProvider  provideMatchQueryWithPHPUnitConstraint
     */
    public function testMatchQueryWithPHPUnitConstraint($constraint, $query, $expected)
    {
        $this->createDatabaseMock()
            ->expects($this->once())
            ->query($constraint)
            ->willReturnResultSet($expected);
        $actual = $this->db->query($query);
        $this->assertSame($expected, $actual);
    }

    public function provideMatchQueryWithPHPUnitConstraint()
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
     * @dataProvider  provideMatchWithQueryMatchersWithConsecutiveCallsBuilder
     */
    public function testMatchWithQueryMatchersWithConsecutiveCallsBuilder($query, $exception, $expecteds)
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
            if ($e instanceof Exception) {
                throw $e;
            }
            $this->assertInstanceOf(get_class($exception), $e);
        }
        $actual2 = $this->db->query($query);
        $this->assertSame($expecteds[2], $actual2);
    }

    public function provideMatchWithQueryMatchersWithConsecutiveCallsBuilder()
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
     * @dataProvider  provideMatchWithQueryMatchersWithCustomCallbackHandlers
     */
    public function testMatchWithQueryMatchersWithCustomCallbackHandlers($constraint, $callback, $query, $expected)
    {
        $this->createDatabaseMock()
            ->expects($this->once())
            ->query($constraint)
            ->willInvokeCallback($callback);
        $actual = $this->db->query($query);
        $this->assertSame($expected, $actual);
    }

    public function provideMatchWithQueryMatchersWithCustomCallbackHandlers()
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

    protected function getDatabaseDriver()
    {
        return $this->db;
    }

    protected function setUp()
    {
        $instance = NULL;
        $this->db = $this->createPartialMock(
            DatabaseDriverInterface::class,
            ['setMockObject', 'query']
        );
        $this->db->expects($this->any())
            ->method('setMockObject')
            ->willReturnCallback(function (Mock $mock) use ( & $instance) {
                if ($instance !== NULL) {
                    throw new LogicException('Mock already set');
                }
                $instance = $mock;
            });
        $this->db->expects($this->any())
            ->method('query')
            ->willReturnCallback(function ($query) use ( & $instance) {
                // A super-simple implementation for a fake database driver.
                $invocation = $instance->invoke($query);
                if (strpos($query, 'SELECT') === 0) {
                    return $invocation->getResultSet();
                }
                elseif (strpos($query, 'INSERT') === 0) {
                    return $invocation->getLastInsertId();
                }
                elseif (strpos($query, 'UPDATE') === 0 || strpos($query, 'DELETE') === 0) {
                    return $invocation->getAffectedRows();
                }
            });
    }

    protected function tearDown()
    {
        $this->db = NULL;
    }
}
