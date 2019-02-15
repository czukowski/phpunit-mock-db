<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\MockObject\MockWrapper;

/**
 * MockTraitTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MockTraitTest extends Testcase
{
    /**
     * @test
     */
    public function testCreateDatabaseMock(): void
    {
        $setMockObject = NULL;
        $registerMockObject = NULL;
        $db = $this->createMock(DatabaseDriverInterface::class);
        $db->expects($this->once())
            ->method('setMockObject')
            ->with($this->callback(
                function ($mock) use ( & $setMockObject) {
                    $this->assertInstanceOf(Mock::class, $mock);
                    $setMockObject = $mock;
                    return TRUE;
                }
            ));
        $methods = ['getDatabaseDriver'];
        $object = $this->getMockForTrait(MockTrait::class, [], '', TRUE, TRUE, TRUE, $methods);
        $object->expects($this->once())
            ->method('getDatabaseDriver')
            ->will($this->returnValue($db));
        $object->expects($this->once())
            ->method('registerMockObject')
            ->with($this->callback(
                function ($mockObject) use ( & $registerMockObject) {
                    $this->assertInstanceOf(MockWrapper::class, $mockObject);
                    $mock = $this->getObjectPropertyValue($mockObject, 'object');
                    $this->assertInstanceOf(Mock::class, $mock);
                    $registerMockObject = $mock;
                    return TRUE;
                }
            ));
        $actual = $object->createDatabaseMock();
        $this->assertSame($actual, $setMockObject);
        $this->assertSame($actual, $registerMockObject);
    }
}
