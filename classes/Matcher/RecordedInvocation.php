<?php
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
    function isAnyInvokedCount();

    /**
     * @return  boolean
     */
    function isNeverInvokedCount();
}
