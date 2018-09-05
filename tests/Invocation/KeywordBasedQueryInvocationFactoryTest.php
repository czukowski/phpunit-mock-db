<?php
namespace Cz\PHPUnit\MockDB\Invocation;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Testcase,
    InvalidArgumentException;

/**
 * KeywordBasedQueryInvocationFactoryTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class KeywordBasedQueryInvocationFactoryTest extends Testcase
{
    /**
     * @dataProvider  provideCreateInvocation
     */
    public function testCreateInvocation($sql, $expected)
    {
        $object = $this->createObject();
        $this->expectExceptionFromArgument($expected);
        $actual = $object->createInvocation($sql);
        $this->assertInstanceOf('Cz\PHPUnit\MockDB\Invocation\QueryInvocation', $actual);
        $this->assertInternalType('callable', $expected);
        call_user_func($expected, $actual);
    }

    public function provideCreateInvocation()
    {
        return [
            $this->createCreateInvocationTestCaseForUpdateKeyword('UPDATE `t1` SET `name` = "foo"'),
            $this->createCreateInvocationTestCaseForUpdateKeyword('DELETE FROM `t1`'),
            $this->createCreateInvocationTestCaseForInsertKeyword('INSERT INTO `t1` SELECT * FROM `t2`'),
            $this->createCreateInvocationTestCaseForInsertKeyword('REPLACE INTO `t1` VALUES ("foo", "bar")'),
            $this->createCreateInvocationTestCaseForSelectKeyword('SELECT * FROM `t`'),
            $this->createCreateInvocationTestCaseForSelectKeyword('SHOW PROCESSLIST'),
            $this->createCreateInvocationTestCaseForSelectKeyword('EXEC [sys].[sp_helpindex]'),
            $this->createCreateInvocationTestCaseForUnknownKeyword('BEGIN'),
            $this->createCreateInvocationTestCaseForUnknownKeyword('COMMIT'),
            $this->createCreateInvocationTestCaseForUnknownKeyword('ROLLBACK'),
            $this->createCreateInvocationTestCaseForException(''),
        ];
    }

    private function createCreateInvocationTestCaseForUpdateKeyword($sql)
    {
        return [
            $sql,
            function (Invocation $actual) {
                $this->assertSame(0, $actual->getAffectedRows());
                $this->assertNull($actual->getLastInsertId());
                $this->assertNull($actual->getResultSet());
            }
        ];
    }

    private function createCreateInvocationTestCaseForInsertKeyword($sql)
    {
        return [
            $sql,
            function (Invocation $actual) {
                $this->assertSame(0, $actual->getAffectedRows());
                $this->assertSame(1, $actual->getLastInsertId());
                $this->assertNull($actual->getResultSet());
            }
        ];
    }

    private function createCreateInvocationTestCaseForSelectKeyword($sql)
    {
        return [
            $sql,
            function (Invocation $actual) {
                $this->assertSame([], $actual->getResultSet());
                $this->assertNull($actual->getAffectedRows());
                $this->assertNull($actual->getLastInsertId());
            }
        ];
    }

    private function createCreateInvocationTestCaseForUnknownKeyword($sql)
    {
        return [
            $sql,
            function (Invocation $actual) {
                $this->assertNull($actual->getAffectedRows());
                $this->assertNull($actual->getLastInsertId());
                $this->assertNull($actual->getResultSet());
            }
        ];
    }

    private function createCreateInvocationTestCaseForException($sql)
    {
        return [$sql, new InvalidArgumentException];
    }

    private function createObject()
    {
        return new KeywordBasedQueryInvocationFactory;
    }
}
