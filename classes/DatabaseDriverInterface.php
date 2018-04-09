<?php
namespace Cz\PHPUnit\MockDB;

/**
 * DatabaseDriverInterface
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface DatabaseDriverInterface
{
    /**
     * @param  Mock  $mock
     */
    public function setMockObject(Mock $mock);
}
