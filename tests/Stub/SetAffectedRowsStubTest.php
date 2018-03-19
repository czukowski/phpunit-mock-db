<?php
namespace Cz\PHPUnit\MockDB\Stub;

/**
 * SetAffectedRowsStubTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class SetAffectedRowsStubTest extends Testcase
{
    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke($count)
    {
        $object = new SetAffectedRowsStub($count);
        $invocation = $this->createInvocationExpectMethod('setAffectedRows', $count);
        $object->invoke($invocation);
    }

    public function provideInvoke()
    {
        return [
            [0],
            [100],
        ];
    }
}
