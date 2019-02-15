<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Exception,
    PHPUnit\Framework\TestCase as FrameworkTestCase,
    ReflectionProperty;

/**
 * Testcase
 * 
 * @author   czukowski
 * @license  MIT License
 */
abstract class Testcase extends FrameworkTestCase
{
    /**
     * @param  mixed  $expected
     */
    public function expectExceptionFromArgument($expected): void
    {
        if ($expected instanceof Exception) {
            $this->expectException(get_class($expected));
        }
    }

    /**
     * @param   object  $object
     * @param   string  $name
     * @return  mixed
     */
    protected function getObjectPropertyValue($object, string $name)
    {
        $property = new ReflectionProperty($object, $name);
        $property->setAccessible(TRUE);
        return $property->getValue($object);
    }
}
