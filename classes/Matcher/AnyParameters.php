<?php declare(strict_types=1);

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
    public function toString(): string
    {
        return 'with any parameters';
    }

    /**
     * @param   BaseInvocation  $invocation
     * @return  boolean
     */
    public function matches(BaseInvocation $invocation): bool
    {
        return TRUE;
    }

    /**
     * @param  BaseInvocation  $invocation
     */
    public function invoked(BaseInvocation $invocation): void
    {}

    public function verify(): void
    {}

}
