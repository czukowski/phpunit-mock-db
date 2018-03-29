<?php
namespace Cz\PHPUnit\MockDB\Doubles;

use Cz\PHPUnit\MockDB\InvocationMocker,
    Cz\PHPUnit\MockDB\Mock,
    Cz\PHPUnit\MockDB\MockObject\InvocationsContainer;

/**
 * MockDouble
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MockDouble extends Mock
{
    private $invocationMocker;
    private $invocationsContainer;

    public function __construct(
        InvocationMocker $invocationMocker,
        InvocationsContainer $invocationsContainer = NULL
    ) {
        $this->invocationMocker = $invocationMocker;
        $this->invocationsContainer = $invocationsContainer;
    }

    public function getInvocationMocker()
    {
        return $this->invocationMocker;
    }

    protected function getInvocationsContainer()
    {
        return $this->invocationsContainer !== NULL
            ? $this->invocationsContainer
            : parent::getInvocationMocker();
    }
}
