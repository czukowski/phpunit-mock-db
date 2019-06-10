<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    PHPUnit\Framework\MockObject\Invocation as MockObjectInvocation;

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
     * @var  array  MockObjectInvocation[]
     */
    private $mockObjectInvocations = [];

    /**
     * @param   BaseInvocation  $invocation
     * @return  MockObjectInvocation
     */
    public function getMockObjectInvocation(BaseInvocation $invocation)
    {
        for ($i = 0; $i < count($this->baseInvocations); $i++) {
            if ($this->baseInvocations[$i] === $invocation) {
                return $this->mockObjectInvocations[$i];
            }
        }
        $mockObjectInvocation = new MockObjectInvocation(get_class($invocation), '', [], '', $invocation);
        $this->baseInvocations[] = $invocation;
        $this->mockObjectInvocations[] = $mockObjectInvocation;
        return $mockObjectInvocation;
    }
}
