<?php
namespace Cz\PHPUnit\MockDB\Stub;

/**
 * ReturnResultSetStubTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ReturnResultSetStubTest extends Testcase
{
    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke($results)
    {
        $object = new ReturnResultSetStub($results);
        $invocation = $this->createInvocationExpectMethod('setResultSet', $results);
        $object->invoke($invocation);
    }

    public function provideInvoke()
    {
        return [
            [
                [
                    ['id' => 1, 'name' => 'foo'],
                    ['id' => 2, 'name' => 'bar'],
                ]
            ],
        ];
    }
}
