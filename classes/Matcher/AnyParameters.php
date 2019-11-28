<?php
namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation;

/**
 * AnyParameters
 * 
 * @author   czukowski
 * @license  MIT License
 */
class AnyParameters implements ParametersMatcher
{
    /**
     * @return  string
     */
    public function toString()
    {
        return 'with any parameters';
    }

    /**
     * @param   BaseInvocation  $invocation
     * @return  boolean
     */
    public function matches(BaseInvocation $invocation)
    {
        return $invocation->getParameters() !== [];
    }

    /**
     * @param  BaseInvocation  $invocation
     */
    public function invoked(BaseInvocation $invocation)
    {}

    public function verify()
    {}

}
