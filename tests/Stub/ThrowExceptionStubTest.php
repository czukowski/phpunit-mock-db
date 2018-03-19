<?php
namespace Cz\PHPUnit\MockDB\Stub;

use RuntimeException;

/**
 * ThrowExceptionStubTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ThrowExceptionStubTest extends Testcase
{
    /**
     * @dataProvider  provideInvoke
     */
    public function testInvoke($exception)
    {
        $object = new ThrowExceptionStub($exception);
        $invocation = $this->createInvocation();
        $this->expectExceptionObject($exception);
        $object->invoke($invocation);
    }

    public function provideInvoke()
    {
        return [
            [new RuntimeException('Boom', -100)],
        ];
    }
}
