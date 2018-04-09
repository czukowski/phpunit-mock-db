<?php
namespace Cz\PHPUnit\MockDB;

use PHPUnit_Framework_SelfDescribing as SelfDescribing;

/**
 * Stub
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface Stub extends SelfDescribing
{
    /**
     * @param  Invocation
     */
    function invoke(Invocation $invocation);
}
