<?php
namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation;

/**
 * InvocationsContainer
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvocationsContainer
{
    /**
     * @var  array  BaseInvocation[]
     */
    private $baseInvocations = [];
    /**
     * @var  array  InvocationWrapper[]
     */
    private $mockObjectInvocations = [];

    /**
     * @param   BaseInvocation  $invocation
     * @return  InvocationWrapper
     */
    public function getMockObjectInvocation(BaseInvocation $invocation)
    {
        for ($i = 0; $i < count($this->baseInvocations); $i++) {
            if ($this->baseInvocations[$i] === $invocation) {
                return $this->mockObjectInvocations[$i];
            }
        }
        $mockObjectInvocation = new InvocationWrapper($invocation);
        $this->baseInvocations[] = $invocation;
        $this->mockObjectInvocations[] = $mockObjectInvocation;
        return $mockObjectInvocation;
    }
}
