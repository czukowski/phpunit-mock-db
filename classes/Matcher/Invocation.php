<?php
namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    PHPUnit_Framework_MockObject_Verifiable as Verifiable,
    PHPUnit_Framework_SelfDescribing as SelfDescribing;

/**
 * Invocation
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface Invocation extends SelfDescribing, Verifiable
{
    /**
     * @param  BaseInvocation $invocation
     */
    function invoked(BaseInvocation $invocation);

    /**
     * @param   BaseInvocation $invocation
     * @return  boolean
     */
    function matches(BaseInvocation $invocation);
}
