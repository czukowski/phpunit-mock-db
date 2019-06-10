<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase,
    PHPUnit\Framework\MockObject\Invocation as MockObjectInvocation;

/**
 * InvocationsContainerTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvocationsContainerTest extends Testcase
{
    /**
     * @dataProvider  provideGetMockObjectInvocation
     */
    public function testGetMockObjectInvocation(array $invocations): void
    {
        $object = new InvocationsContainer;
        $previousInvocations = [];
        foreach ($invocations as $invocation) {
            $wrappedInvocation = $object->getMockObjectInvocation($invocation);
            $this->assertInstanceOf(MockObjectInvocation::class, $wrappedInvocation);
            $sameWrappedInvocation = $object->getMockObjectInvocation($invocation);
            $this->assertSame($wrappedInvocation, $sameWrappedInvocation);
            foreach ($previousInvocations as $previousWrappedInvocation) {
                $this->assertNotSame($wrappedInvocation, $previousWrappedInvocation);
            }
            $previousInvocations[] = $wrappedInvocation;
        }
    }

    public function provideGetMockObjectInvocation(): array
    {
        return [
            $this->createGetMockObjectInvocationTestCase(1),
            $this->createGetMockObjectInvocationTestCase(5),
        ];
    }

    private function createGetMockObjectInvocationTestCase(int $invocationsCount): array
    {
        return [
            array_map(
                function () {
                    return $this->createMock(BaseInvocation::class);
                },
                array_fill(0, $invocationsCount, NULL)
            ),
        ];
    }
}
