<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Stub,
    PHPUnit\Framework\MockObject\RuntimeException,
    InvalidArgumentException;

/**
 * Stack
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ConsecutiveCallsStub implements Stub
{
    /**
     * @var  array  Stub[]
     */
    private $stack;
    /**
     * @var  Stub
     */
    private $current;

    /**
     * @param   array  $stack
     * @throws  InvalidArgumentException
     */
    public function __construct(array $stack = [])
    {
        foreach ($stack as $item) {
            if ( ! $item instanceof Stub) {
                throw new InvalidArgumentException('All items in stack must implement `Cz\PHPUnit\MockDB\Stub`');
            }
        }
        $this->stack = $stack;
    }

    /**
     * @param  Stub  $stub
     */
    public function addStub(Stub $stub): void
    {
        $this->stack[] = $stub;
    }

    /**
     * @param  Invocation  $invocation
     */
    public function invoke(Invocation $invocation): void
    {
        $this->current = array_shift($this->stack);
        if ( ! $this->current) {
            throw new RuntimeException('No more items left in stack');
        }
        $this->current->invoke($invocation);
    }

    /**
     * @return  boolean
     */
    public function toString(): string
    {
        if ($this->current) {
            return $this->current->toString();
        }
        else {
            return sprintf('stack of %s item(s)', count($this->stack));
        }
    }
}
