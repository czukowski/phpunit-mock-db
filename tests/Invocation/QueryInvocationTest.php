<?php
namespace Cz\PHPUnit\MockDB\Invocation;

use Cz\PHPUnit\MockDB\Testcase,
    ArrayObject;

/**
 * QueryInvocationTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class QueryInvocationTest extends Testcase
{
    /**
     * @dataProvider  provideGetQuery
     */
    public function testGetQuery($query)
    {
        $object = $this->createObject($query);
        $this->assertSame($query, $object->getQuery());
    }

    public function provideGetQuery()
    {
        return [
            ['SELECT * FROM `t`'],
        ];
    }

    /**
     * @dataProvider  providerParameters
     */
    public function testParameters($query, $parameters)
    {
        $object = $this->createObject($query);
        $object->setParameters($parameters);
        $this->assertSame($parameters, $object->getParameters());
    }

    public function providerParameters()
    {
        return [
            ['SELECT * FROM `t1` WHERE `c` = ?', [1]],
        ];
    }

    /**
     * @dataProvider  provideAffectedRows
     */
    public function testAffectedRows($query, $value)
    {
        $object = $this->createObject($query);
        $object->setAffectedRows($value);
        $this->assertSame($value, $object->getAffectedRows());
    }

    public function provideAffectedRows()
    {
        return [
            ['UPDATE `t` SET `a` = 1 WHERE `b` = 2', 100],
        ];
    }

    /**
     * @dataProvider  provideLastInsertId
     */
    public function testLastInsertId($query, $value)
    {
        $object = $this->createObject($query);
        $object->setLastInsertId($value);
        $this->assertSame($value, $object->getLastInsertId());
    }

    public function provideLastInsertId()
    {
        return [
            ['INSERT INTO `t` (`a`, `b`) VALUES (1, 2)', 505],
        ];
    }

    /**
     * @dataProvider  provideResultSet
     */
    public function testResultSet($query, $results)
    {
        $object = $this->createObject($query);
        $object->setResultSet($results);
        $this->assertSame($results, $object->getResultSet());
    }

    public function provideResultSet()
    {
        return [
            [
                'SELECT * FROM `t1`',
                [],
            ],
            [
                'SELECT * FROM `t2`',
                [
                    ['id' => 1, 'name' => 'foo'],
                    ['id' => 2, 'name' => 'bar'],
                ],
            ],
            [
                'SELECT * FROM `t3`',
                new ArrayObject([
                    ['id' => 1, 'name' => 'foo'],
                    ['id' => 2, 'name' => 'bar'],
                ]),
            ],
        ];
    }

    private function createObject($query)
    {
        return new QueryInvocation($query);
    }
}
