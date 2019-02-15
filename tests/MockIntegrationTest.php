<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\Stub,
    PHPUnit\Framework\ExpectationFailedException,
    RuntimeException,
    Throwable;

/**
 * MockIntegrationTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MockIntegrationTest extends Testcase
{
    /**
     * @dataProvider  provideMock
     */
    public function testMock(array $matchers, array $invocations, bool $willVerify): void
    {
        $mock = $this->createObject();
        $this->setupMatchers($mock, $matchers);
        $this->callInvocations($mock, $invocations);
        if ( ! $willVerify) {
            $this->expectExceptionFromArgument($this->createExpectationFailedException());
        }
        $this->assertNull($mock->verify());  // At the very least, assert this to avoid 'risky' test.
    }

    private function setupMatchers(Mock $mock, array $matchers): void
    {
        foreach ($matchers as $options) {
            // `$im` is being reassigned multiple times to emulate calls chaining.
            $im = $mock->expects($options['expects']);
            if (isset($options['query'])) {
                $im = $im->query($options['query']);
            }
            if (isset($options['will'])) {
                $im = $im->will($options['will']);
            }
            if (isset($options['onConsecutiveCalls'])) {
                $im = $im->onConsecutiveCalls();
                foreach ($options['onConsecutiveCalls'] as $call) {
                    $im->will($call);
                }
                $im = $im->done();
            }
        }
    }

    private function callInvocations(Mock $mock, array $invocations): void
    {
        foreach ($invocations as $options) {
            try {
                $invocation = $mock->invoke($options['invoke']);
            }
            catch (Throwable $e) {
                if ( ! $options['expected'] instanceof Throwable) {
                    throw $e;
                }
                $this->assertInstanceOf(get_class($options['expected']), $e);
                continue;
            }
            if ($options['expected'] instanceof Throwable) {
                $this->fail('Expected exception not thrown');
            }
            $actual = $invocation->{$options['result']}();
            $this->assertEquals($options['expected'], $actual);
        }
    }

    public function provideMock(): array
    {
        $resultSet1 = [['foo' => 'bar']];
        $resultSet2 = [['no' => 'way']];
        return [
            /**
             * $mock->expects($this->any())
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('SELECT * FROM `t`');
             */
            'Match any query, any invocation count' => [
                [
                    [
                        'expects' => $this->any(),
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'SELECT * FROM `t`',
                        'result' => 'getResultSet',
                        'expected' => $resultSet1,
                    ],
                ],
                TRUE,
            ],
            /**
             * $mock->expects($this->once())
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('SELECT * FROM `t`');
             */
            'Match any query, single invocation' => [
                [
                    [
                        'expects' => $this->once(),
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'SELECT * FROM `t`',
                        'result' => 'getResultSet',
                        'expected' => $resultSet1,
                    ],
                ],
                TRUE,
            ],
            /**
             * $mock->expects($this->once())
             *     ->willReturnResultSet($resultSet1);
             */
            'Match any query, single invocation, none invoked' => [
                [
                    [
                        'expects' => $this->once(),
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [],
                FALSE,
            ],
            /**
             * $mock->expects($this->any())
             *     ->willReturnResultSet($resultSet1);
             */
            'Match any query, any invocation count, none invoked' => [
                [
                    [
                        'expects' => $this->any(),
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [],
                TRUE,
            ],
            /**
             * $mock->expects($this->once())
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('SELECT * FROM `t1`');
             * $mock->invoke('SELECT * FROM `t2`');
             */
            'Match any query, single invocation, more invoked' => [
                [
                    [
                        'expects' => $this->once(),
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'SELECT * FROM `t1`',
                        'result' => 'getResultSet',
                        'expected' => $resultSet1,
                    ],
                    [
                        'invoke' => 'SELECT * FROM `t2`',
                        'expected' => $this->createExpectationFailedException(),
                    ],
                ],
                FALSE,
            ],
            /**
             * $mock->expects($this->at(2))
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('SELECT * FROM `t1`');
             */
            'Match any query only at 2nd invocation' => [
                [
                    [
                        'expects' => $this->at(2),
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'SELECT * FROM `t1`',
                        'expected' => $this->createExpectationFailedException(),
                    ],
                ],
                FALSE,
            ],
            /**
             * $mock->expects($this->once())
             *     ->query('SELECT * FROM `t2`')
             *     ->willReturnResultSet($resultSet2);
             * $mock->expects($this->once())
             *     ->query('SELECT * FROM `t1`')
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('SELECT * FROM `t1`');
             * $mock->invoke('SELECT * FROM `t2`');
             */
            'Match with query matchers, once each' => [
                [
                    [
                        'expects' => $this->once(),
                        'query' => 'SELECT * FROM `t2`',
                        'will' => new Stub\ReturnResultSetStub($resultSet2),
                    ],
                    [
                        'expects' => $this->once(),
                        'query' => 'SELECT * FROM `t1`',
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'SELECT  *  FROM  `t1`',
                        'result' => 'getResultSet',
                        'expected' => $resultSet1,
                    ],
                    [
                        'invoke' => 'SELECT  *  FROM  `t2`',
                        'result' => 'getResultSet',
                        'expected' => $resultSet2,
                    ],
                ],
                TRUE,
            ],
            /**
             * $mock->expects($this->at(1))
             *     ->query('INSERT INTO `t1` VALUES (1, 2, 3)')
             *     ->willSetLastInsertId(1);
             * $mock->expects($this->at(2))
             *     ->query('INSERT INTO `t1` VALUES (1, 2, 3)')
             *     ->willSetLastInsertId(2);
             * $mock->expects($this->once())
             *     ->query('SELECT * FROM `t1`')
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('SELECT * FROM `t1`');
             * $mock->invoke('INSERT INTO `t1` VALUES (1, 2, 3)');
             * $mock->invoke('INSERT INTO `t1` VALUES (1, 2, 3)');
             */
            'Match mixed queries with query matchers, once each' => [
                [
                    [
                        'expects' => $this->at(1),
                        'query' => 'INSERT INTO `t1` VALUES (1, 2, 3)',
                        'will' => new Stub\SetLastInsertIdStub(1),
                    ],
                    [
                        'expects' => $this->at(2),
                        'query' => 'INSERT INTO `t1` VALUES (1, 2, 3)',
                        'will' => new Stub\SetLastInsertIdStub(2),
                    ],
                    [
                        'expects' => $this->once(),
                        'query' => 'SELECT * FROM `t1`',
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'SELECT * FROM  `t1`',
                        'result' => 'getResultSet',
                        'expected' => $resultSet1,
                    ],
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES (1, 2, 3)',
                        'result' => 'getLastInsertId',
                        'expected' => 1,
                    ],
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES (1, 2, 3)',
                        'result' => 'getLastInsertId',
                        'expected' => 2,
                    ],
                ],
                TRUE,
            ],
            /**
             * $mock->expects($this->exactly(3))
             *     ->query('INSERT INTO `t1` VALUES ("a", "b", "c")')
             *     ->willSetLastInsertId(1, 2, 3);
             * $mock->invoke('INSERT INTO `t1` VALUES ("a", "b", "c")');
             * $mock->invoke('INSERT INTO `t1` VALUES ("a", "b", "c")');
             * $mock->invoke('INSERT INTO `t1` VALUES ("a", "b", "c")');
             */
            'Match with query matchers, with consecutive calls' => [
                [
                    [
                        'expects' => $this->exactly(3),
                        'query' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'will' => new Stub\ConsecutiveCallsStub([
                            new Stub\SetLastInsertIdStub(1),
                            new Stub\SetLastInsertIdStub(2),
                            new Stub\SetLastInsertIdStub(3),
                        ]),
                    ],
                ],
                [
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'result' => 'getLastInsertId',
                        'expected' => 1,
                    ],
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'result' => 'getLastInsertId',
                        'expected' => 2,
                    ],
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'result' => 'getLastInsertId',
                        'expected' => 3,
                    ],
                ],
                TRUE,
            ],
            /**
             * $mock->expects($this->once())
             *     ->query('SELECT * FROM `t1`')
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('SELECT * FROM `t2`');
             */
            'Match with query matcher, invoke different query' => [
                [
                    [
                        'expects' => $this->once(),
                        'query' => 'SELECT * FROM `t1`',
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'SELECT * FROM `t2`',
                        'expected' => $this->createExpectationFailedException(),
                    ],
                ],
                FALSE,
            ],
            /**
             * $mock->expects($this->exactly(2))
             *     ->query('UPDATE `t1` SET `foo` = "bar"')
             *     ->willSetAffectedRows(1);
             * $mock->invoke('UPDATE `t1` SET `foo` = "bar"');
             * $mock->invoke('UPDATE `t1` SET `foo` = "bar"');
             */
            'Match with query matcher, assert affected rows' => [
                [
                    [
                        'expects' => $this->exactly(2),
                        'query' => 'UPDATE `t1` SET `foo` = "bar"',
                        'will' => new Stub\SetAffectedRowsStub(1),
                    ],
                ],
                [
                    [
                        'invoke' => 'UPDATE `t1` SET `foo` = "bar"',
                        'result' => 'getAffectedRows',
                        'expected' => 1,
                    ],
                    [
                        'invoke' => 'UPDATE `t1` SET `foo` = "bar"',
                        'result' => 'getAffectedRows',
                        'expected' => 1,
                    ],
                ],
                TRUE,
            ],
            /**
             * $mock->expects($this->once())
             *     ->query($this->stringStartsWith('SELECT'))
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('SELECT * FROM `t`');
             */
            'Match query with PHPUnit constraint' => [
                [
                    [
                        'expects' => $this->once(),
                        'query' => $this->stringStartsWith('SELECT'),
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'SELECT * FROM `t`',
                        'result' => 'getResultSet',
                        'expected' => $resultSet1,
                    ],
                ],
                TRUE,
            ],
            /**
             * $mock->expects($this->once())
             *     ->query($this->stringStartsWith('SELECT'))
             *     ->willReturnResultSet($resultSet1);
             * $mock->invoke('UPDATE `t` SET `foo` = "bar"');
             */
            'Not match query with PHPUnit constraint' => [
                [
                    [
                        'expects' => $this->once(),
                        'query' => $this->stringStartsWith('SELECT'),
                        'will' => new Stub\ReturnResultSetStub($resultSet1),
                    ],
                ],
                [
                    [
                        'invoke' => 'UPDATE `t` SET `foo` = "bar"',
                        'expected' => $this->createExpectationFailedException(),
                    ],
                ],
                FALSE,
            ],
            /**
             * $mock->expects($this->exactly(4))
             *     ->query('INSERT INTO `t1` VALUES ("a", "b", "c")')
             *     ->onConsecutiveCalls()
             *     ->willSetLastInsertId(1)
             *     ->willSetLastInsertId(2)
             *     ->willThrowException(new RuntimeException('Deadlock'))
             *     ->willSetLastInsertId(3);
             * $mock->invoke('INSERT INTO `t1` VALUES ("a", "b", "c")');
             * $mock->invoke('INSERT INTO `t1` VALUES ("a", "b", "c")');
             * $mock->invoke('INSERT INTO `t1` VALUES ("a", "b", "c")');
             * $mock->invoke('INSERT INTO `t1` VALUES ("a", "b", "c")');
             */
            'Match with query matchers, with consecutive calls builder' => [
                [
                    [
                        'expects' => $this->exactly(4),
                        'query' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'onConsecutiveCalls' => [
                            new Stub\SetLastInsertIdStub(1),
                            new Stub\SetLastInsertIdStub(2),
                            new Stub\ThrowExceptionStub(new RuntimeException('Deadlock')),
                            new Stub\SetLastInsertIdStub(3),
                        ],
                    ],
                ],
                [
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'result' => 'getLastInsertId',
                        'expected' => 1,
                    ],
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'result' => 'getLastInsertId',
                        'expected' => 2,
                    ],
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'expected' => new RuntimeException,
                    ],
                    [
                        'invoke' => 'INSERT INTO `t1` VALUES ("a", "b", "c")',
                        'result' => 'getLastInsertId',
                        'expected' => 3,
                    ],
                ],
                TRUE,
            ],
        ];
    }

    private function createExpectationFailedException(): ExpectationFailedException
    {
        return new ExpectationFailedException('');
    }

    private function createObject($requireMatch = TRUE): Mock
    {
        $object = new Mock;
        $object->setRequireMatch($requireMatch);
        return $object;
    }
}
