<?php
namespace Cz\PHPUnit\MockDB;

use Exception,
    PHPUnit_Framework_TestCase as FrameworkTestCase;

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

    /**
     * Backported from higher version of PHPUnit.
     * 
     * @copyright  (c) Sebastian Bergmann <sebastian@phpunit.de>
     * 
     * @param  Exception  $exception
     */
    public function expectExceptionObject(Exception $exception)
    {
        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());
        $this->expectExceptionCode($exception->getCode());
    }
}
