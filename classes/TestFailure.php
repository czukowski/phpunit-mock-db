<?php
namespace Cz\PHPUnit\MockDB;

use PHPUnit_Framework_TestFailure as FrameworkTestFailure,
    Exception;

/**
 * TestFailure
 * 
 * @author   czukowski
 * @license  MIT License
 */
class TestFailure
{
    /**
     * @param   Exception  $error
     * @return  string
     */
    public static function exceptionToString(Exception $error)
    {
        $message = FrameworkTestFailure::exceptionToString($error);
        return preg_replace('#^Method#', 'Database', $message);
    }
}
