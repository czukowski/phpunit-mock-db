<?php declare(strict_types=1);

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
    public function testInvoke(array $results): void
    {
        $object = new ReturnResultSetStub($results);
        $invocation = $this->createInvocationExpectMethod('setResultSet', $results);
        $object->invoke($invocation);
    }

    public function provideInvoke(): array
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
