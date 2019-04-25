<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Matcher;

/**
 * RecordedInvocation
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface RecordedInvocation extends Invocation
{
    /**
     * @return  boolean
     */
    function isAnyInvokedCount(): bool;

    /**
     * @return  boolean
     */
    function isNeverInvokedCount(): bool;
}
