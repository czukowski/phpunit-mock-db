<?php
namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as MockDBInvocation,
    PHPUnit\Framework\MockObject\Invocation as MockObjectBaseInvocation;

/**
 * InvocationWrapper
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvocationWrapper implements MockObjectBaseInvocation
{
    /**
     * @var  MockDBInvocation
     */
    private $invocation;

    /**
     * @param  MockDBInvocation  $invocation
     */
    public function __construct(MockDBInvocation $invocation)
    {
        $this->invocation = $invocation;
    }

    /**
     * @return  string
     */
    public function getClassName(): string
    {
        return '';
    }

    /**
     * @return  string
     */
    public function getMethodName(): string
    {
        return '';
    }

    /**
     * @return  array
     */
    public function getParameters(): array
    {
        return [];
    }

    /**
     * @return  string
     */
    public function getReturnType(): string
    {
        return '';
    }

    /**
     * @return  boolean
     */
    public function isReturnTypeNullable(): bool
    {
        return TRUE;
    }

    /**
     * @return  NULL
     */
    public function generateReturnValue()
    {
        return;
    }

    /**
     * @return  string
     */
    public function toString()
    {
        return 'Database';
    }
}
