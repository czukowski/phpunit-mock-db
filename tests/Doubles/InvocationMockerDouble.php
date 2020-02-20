<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Doubles;

use Cz\PHPUnit\MockDB\Builder\InvocationMocker as InvocationMockerBuilder,
    Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\InvocationMocker,
    Cz\PHPUnit\MockDB\Matcher\RecordedInvocation;

/**
 * InvocationMockerDouble
 * 
 * @author   czukowski
 * @license  MIT License
 */
class InvocationMockerDouble extends InvocationMocker
{
    public $invoked;
    public $matcher;

    public function expects(RecordedInvocation $matcher): InvocationMockerBuilder
    {
        $this->matcher = $matcher;
        return parent::expects($matcher);
    }

    public function invoke(Invocation $invocation): void
    {
        $this->invoked = $invocation;
        parent::invoke($invocation);
    }
}
