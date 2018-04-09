<?php
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
        $expectedInstanceOf,
        $attribute
    ) {
        $this->assertInstanceOf('Cz\PHPUnit\MockDB\Stub\ConsecutiveCallsStub', $stub);
        $stack = $this->getObjectAttribute($stub, 'stack');
        $this->assertInternalType('array', $stack);
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
        $expectedInstanceOf,
        $attribute,
        $expectedAttribute
    ) {
        $this->assertInstanceOf($expectedInstanceOf, $stub);
        $actual = $this->getObjectAttribute($stub, $attribute);
        $this->assertSame($expectedAttribute, $actual);
    }
}
