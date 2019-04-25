<?php declare(strict_types=1);

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
    function invoked(BaseInvocation $invocation): void;

    /**
     * @param   BaseInvocation $invocation
     * @return  boolean
     */
    function matches(BaseInvocation $invocation): bool;
}
