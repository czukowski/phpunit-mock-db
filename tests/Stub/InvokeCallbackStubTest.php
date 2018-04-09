<?php
namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation;

/**
 * InvokeCallbackStubTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvokeCallbackStubTest extends Testcase
{
    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke($callback, $method, $argument)
    {
        $object = new InvokeCallbackStub($callback);
        $invocation = $this->createInvocationExpectMethod($method, $argument);
        $object->invoke($invocation);
    }

    public function provideInvoke()
    {
        return [
            [
                function (Invocation $invocation) {
                    $invocation->setResultSet([
                        ['id' => 1, 'name' => 'foo'],
                        ['id' => 2, 'name' => 'bar'],
                    ]);
                },
                'setResultSet',
                [
                    ['id' => 1, 'name' => 'foo'],
                    ['id' => 2, 'name' => 'bar'],
                ],
            ],
            [
                function (Invocation $invocation) {
                    $invocation->setAffectedRows(5);
                },
                'setAffectedRows',
                5,
            ],
            [
                function (Invocation $invocation) {
                    $invocation->setLastInsertId(8);
                },
                'setLastInsertId',
                8,
            ],
        ];
    }
}
