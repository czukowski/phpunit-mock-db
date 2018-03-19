<?php
namespace Cz\PHPUnit\MockDB\Stub;

/**
 * SetLastInsertIdStubTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class SetLastInsertIdStubTest extends Testcase
{
    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke($value)
    {
        $object = new SetLastInsertIdStub($value);
        $invocation = $this->createInvocationExpectMethod('setLastInsertId', $value);
        $object->invoke($invocation);
    }

    public function provideInvoke()
    {
        return [
            [123],
        ];
    }
}
