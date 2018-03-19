<?php
namespace Cz\PHPUnit\MockDB;

use Exception,
    PHPUnit\Framework\TestCase as FrameworkTestCase;

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
    public function expectExceptionFromArgument($expected)
    {
        if ($expected instanceof Exception) {
            $this->expectException(get_class($expected));
        }
    }
}
