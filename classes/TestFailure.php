<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use PHPUnit\Framework\TestFailure as FrameworkTestFailure,
    Throwable;

/**
 * TestFailure
 * 
 * @author   czukowski
 * @license  MIT License
 */
class TestFailure
{
    /**
     * @param   Throwable  $error
     * @return  string
     */
    public static function exceptionToString(Throwable $error): string
    {
        $message = FrameworkTestFailure::exceptionToString($error);
        return preg_replace('#^Method#', 'Database', $message);
    }
}
