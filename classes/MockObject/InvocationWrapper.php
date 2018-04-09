<?php
namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Invocation as MockDBInvocation,
    PHPUnit_Framework_MockObject_Invocation as MockObjectBaseInvocation;

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
    public function getClassName()
    {
        return '';
    }

    /**
     * @return  string
     */
    public function getMethodName()
    {
        return '';
    }

    /**
     * @return  array
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * @return  string
     */
    public function getReturnType()
    {
        return '';
    }

    /**
     * @return  boolean
     */
    public function isReturnTypeNullable()
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
