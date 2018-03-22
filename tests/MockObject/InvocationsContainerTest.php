<?php
namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase;

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
    public function testGetMockObjectInvocation($invocations)
    {
        $object = new InvocationsContainer;
        $previousInvocations = [];
        foreach ($invocations as $invocation) {
            $wrappedInvocation = $object->getMockObjectInvocation($invocation);
            $this->assertInstanceOf(InvocationWrapper::class, $wrappedInvocation);
            $sameWrappedInvocation = $object->getMockObjectInvocation($invocation);
            $this->assertSame($wrappedInvocation, $sameWrappedInvocation);
            foreach ($previousInvocations as $previousWrappedInvocation) {
                $this->assertNotSame($wrappedInvocation, $previousWrappedInvocation);
            }
            $previousInvocations[] = $wrappedInvocation;
        }
    }

    public function provideGetMockObjectInvocation()
    {
        return [
            $this->createGetMockObjectInvocationTestCase(1),
            $this->createGetMockObjectInvocationTestCase(5),
        ];
    }

    private function createGetMockObjectInvocationTestCase($invocationsCount)
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
