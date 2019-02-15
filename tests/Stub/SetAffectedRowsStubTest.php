<?php declare(strict_types=1);

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
    public function testInvoke(int $count): void
    {
        $object = new SetAffectedRowsStub($count);
        $invocation = $this->createInvocationExpectMethod('setAffectedRows', $count);
        $object->invoke($invocation);
    }

    public function provideInvoke(): array
    {
        return [
            [0],
            [100],
        ];
    }
}
