<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Stub;

use RuntimeException,
    Throwable;

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
    public function testInvoke(Throwable $exception): void
    {
        $object = new ThrowExceptionStub($exception);
        $invocation = $this->createInvocation();
        $this->expectExceptionObject($exception);
        $object->invoke($invocation);
    }

    public function provideInvoke(): array
    {
        return [
            [new RuntimeException('Boom', -100)],
        ];
    }
}
