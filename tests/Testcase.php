<?php
namespace Cz\PHPUnit\MockDB;

use Exception,
    PHPUnit_Framework_MockObject_MockObject,
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
     * @param   string  $originalClassName
     * @return  PHPUnit_Framework_MockObject_MockObject
     */
    public function createMock($originalClassName)
    {
        return $this->getMock($originalClassName, [], [], '', FALSE);
    }

    /**
     * @param   string  $originalClassName
     * @param   array   $methods
     * @return  PHPUnit_Framework_MockObject_MockObject
     */
    public function createPartialMock($originalClassName, array $methods = [])
    {
        return $this->getMock($originalClassName, $methods, [], '', FALSE);
    }

    /**
     * @param  mixed  $expected
     */
    public function expectExceptionFromArgument($expected)
    {
        if ($expected instanceof Exception) {
            $this->setExpectedException(get_class($expected));
        }
    }

    /**
     * Backported from higher version of PHPUnit.
     * 
     * @copyright  (c) Sebastian Bergmann <sebastian@phpunit.de>
     * 
     * @param  string  $exceptionName
     */
    public function expectException($exceptionName)
    {
        $this->setExpectedException($exceptionName);
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
        $this->setExpectedException(get_class($exception), $exception->getMessage(), $exception->getCode());
    }
}
