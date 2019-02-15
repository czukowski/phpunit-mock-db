<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use PHPUnit\Framework\SelfDescribing;

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
