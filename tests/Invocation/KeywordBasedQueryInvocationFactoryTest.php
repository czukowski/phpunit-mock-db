<?php declare(strict_types=1);

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
    public function testCreateInvocation(string $sql, $expected): void
    {
        $object = $this->createObject();
        $this->expectExceptionFromArgument($expected);
        $actual = $object->createInvocation($sql);
        $this->assertInstanceOf(QueryInvocation::class, $actual);
        $this->assertIsCallable($expected);
        call_user_func($expected, $actual);
    }

    public function provideCreateInvocation(): array
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

    private function createCreateInvocationTestCaseForUpdateKeyword(string $sql): array
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

    private function createCreateInvocationTestCaseForInsertKeyword(string $sql): array
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

    private function createCreateInvocationTestCaseForSelectKeyword(string $sql): array
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

    private function createCreateInvocationTestCaseForUnknownKeyword(string $sql): array
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

    private function createCreateInvocationTestCaseForException(string $sql): array
    {
        return [$sql, new InvalidArgumentException];
    }

    private function createObject(): KeywordBasedQueryInvocationFactory
    {
        return new KeywordBasedQueryInvocationFactory;
    }
}
