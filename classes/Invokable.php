<?php
namespace Cz\PHPUnit\MockDB;

use PHPUnit_Framework_MockObject_Verifiable as Verifiable;

/**
 * Invokable
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface Invokable extends Verifiable
{
    /**
     * @param  Invocation  $invocation
     */
    function invoke(Invocation $invocation);

    /**
     * @param   Invocation  $invocation
     * @return  boolean
     */
    function matches(Invocation $invocation);
}
