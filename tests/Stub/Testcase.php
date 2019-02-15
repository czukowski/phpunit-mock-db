<?php declare(strict_types=1);

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
    protected function createInvocationExpectMethod(string $name, $argument): Invocation
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
    protected function createInvocation(array $skipMethods = []): Invocation
    {
        $invocation = $this->createMock(Invocation::class);
        foreach ($this->getAbstractMethods(Invocation::class) as $name) {
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
    private function getAbstractMethods(string $className): array
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
