<?php
namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB,
    Cz\PHPUnit\MockDB\Invocation,
    ReflectionClass,
    ReflectionMethod;

/**
 * Testcase
 * 
 * @author   czukowski
 * @license  MIT License
 */
abstract class Testcase extends MockDB\Testcase
{
    /**
     * @param   string  $name
     * @param   mixed   $argument
     * @return  Invocation
     */
    protected function createInvocationExpectMethod($name, $argument)
    {
        $invocation = $this->createInvocation([$name]);
        $invocation->expects($this->once())
            ->method($name)
            ->with($argument);
        return $invocation;
    }

    /**
     * @param   array  $skipMethods
     * @return  Invocation
     */
    protected function createInvocation(array $skipMethods = [])
    {
        $invocation = $this->createMock('Cz\PHPUnit\MockDB\Invocation');
        foreach ($this->getAbstractMethods('Cz\PHPUnit\MockDB\Invocation') as $name) {
            if ( ! in_array($name, $skipMethods)) {
                $invocation->expects($this->never())
                    ->method($name);
            }
        }
        return $invocation;
    }

    /**
     * @param   string  $className
     * @return  array
     */
    private function getAbstractMethods($className)
    {
        $class = new ReflectionClass($className);
        return array_map(
            function (ReflectionMethod $method) {
                return $method->getName();
            },
            array_filter(
                $class->getMethods(),
                function (ReflectionMethod $method) {
                    return $method->isAbstract();
                }
            )
        );
    }
}
