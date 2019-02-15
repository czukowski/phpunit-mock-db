<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Doubles;

use Cz\PHPUnit\MockDB\Invocation,
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

    public function expects(RecordedInvocation $matcher)
    {
        $this->matcher = $matcher;
        return parent::expects($matcher);
    }

    public function invoke(Invocation $invocation)
    {
        $this->invoked = $invocation;
        return parent::invoke($invocation);
    }
}
