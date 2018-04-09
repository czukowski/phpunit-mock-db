<?php
namespace Cz\PHPUnit\MockDB;

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
    public function testCreateDatabaseMock()
    {
        $setMockObject = NULL;
        $db = $this->createMock('Cz\PHPUnit\MockDB\DatabaseDriverInterface');
        $db->expects($this->once())
            ->method('setMockObject')
            ->with($this->callback(
                function ($mock) use ( & $setMockObject) {
                    $this->assertInstanceOf('Cz\PHPUnit\MockDB\Mock', $mock);
                    $setMockObject = $mock;
                    return TRUE;
                }
            ));
        $methods = ['getDatabaseDriver'];
        $object = $this->getMockForTrait('Cz\PHPUnit\MockDB\MockTrait', [], '', TRUE, TRUE, TRUE, $methods);
        $object->expects($this->once())
            ->method('getDatabaseDriver')
            ->will($this->returnValue($db));
        $actual = $object->createDatabaseMock();
        $this->assertSame($actual, $setMockObject);
    }
}
