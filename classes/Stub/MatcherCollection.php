<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Matcher\Invocation;

/**
 * MatcherCollection
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface MatcherCollection
{
    /**
     * @param  Invocation  $matcher
     */
    public function addMatcher(Invocation $matcher): void;
}
