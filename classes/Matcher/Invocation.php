<?php
namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    PHPUnit\Framework\MockObject\Verifiable,
    PHPUnit\Framework\SelfDescribing;

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
     * @param  BaseInvocation $invocation
     */
    function matches(BaseInvocation $invocation);
}
