<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    Cz\PHPUnit\MockDB\Testcase;

/**
 * AnyParametersTest
 * 
 * @author   czukowski
 * @license  MIT License
 */
class AnyParametersTest extends Testcase
{
    public function testToString(): void
    {
        $object = $this->createObject();
        $actual = $object->toString();
        $this->assertSame('with any parameters', $actual);
    }

    public function testMatches(): void
    {
        $object = $this->createObject();
        $invocation = $this->createMock(BaseInvocation::class);
        $actual = $object->matches($invocation);
        $this->assertTrue($actual);
    }

    public function testInvoked(): void
    {
        $object = $this->createObject();
        $invocation = $this->createMock(BaseInvocation::class);
        $actual = $object->invoked($invocation);
        $this->assertNull($actual);
    }

    public function testVerify(): void
    {
        $object = $this->createObject();
        $actual = $object->verify();
        $this->assertNull($actual);
    }

    private function createObject(): AnyParameters
    {
        return new AnyParameters;
    }
}
