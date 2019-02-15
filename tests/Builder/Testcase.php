<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Builder;

use Cz\PHPUnit\MockDB,
    Cz\PHPUnit\MockDB\Stub;

/**
 * Testcase
 * 
 * @author   czukowski
 * @license  MIT License
 */
abstract class Testcase extends MockDB\Testcase
{
    /**
     * @param  Stub    $stub
     * @param  array   $expectedItems
     * @param  string  $expectedInstanceOf
     * @param  string  $attribute
     */
    protected function assertConsecutiveStubs(
        Stub $stub,
        array $expectedItems,
        string $expectedInstanceOf,
        string $attribute
    ): void
    {
        $this->assertInstanceOf(Stub\ConsecutiveCallsStub::class, $stub);
        $stack = $this->getObjectPropertyValue($stub, 'stack');
        $this->assertIsArray($stack);
        $this->assertCount(count($expectedItems), $stack);
        for ($i = 0; $i < count($expectedItems); $i++) {
            $this->assertStub($stack[$i], $expectedInstanceOf, $attribute, $expectedItems[$i]);
        }
    }

    /**
     * @param  Stub    $stub
     * @param  string  $expectedInstanceOf
     * @param  string  $attribute
     * @param  mixed   $expectedAttribute
     */
    protected function assertStub(
        Stub $stub,
        string $expectedInstanceOf,
        string $attribute,
        $expectedAttribute
    ): void
    {
        $this->assertInstanceOf($expectedInstanceOf, $stub);
        $actual = $this->getObjectPropertyValue($stub, $attribute);
        $this->assertSame($expectedAttribute, $actual);
    }
}
