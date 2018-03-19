<?php
namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Stub;

/**
 * SetAffectedRowsStub
 * 
 * @author   czukowski
 * @license  MIT License
 */
class SetAffectedRowsStub implements Stub
{
    /**
     * @var  mixed
     */
    private $value;

    /**
     * @param  integer  $affectedRowsCount
     */
    public function __construct($affectedRowsCount)
    {
        $this->value = $affectedRowsCount;
    }

    /**
     * @param  Invocation  $invocation
     */
    public function invoke(Invocation $invocation)
    {
        $invocation->setAffectedRows($this->value);
    }

    /**
     * @return  string
     */
    public function toString()
    {
        return 'set affected rows count';
    }
}
