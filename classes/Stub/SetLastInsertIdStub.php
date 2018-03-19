<?php
namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Stub;

/**
 * SetLastInsertIdStub
 * 
 * @author   czukowski
 * @license  MIT License
 */
class SetLastInsertIdStub implements Stub
{
    /**
     * @var  mixed
     */
    private $value;

    /**
     * @param  mixed  $lastInsertId
     */
    public function __construct($lastInsertId)
    {
        $this->value = $lastInsertId;
    }

    /**
     * @param  Invocation  $invocation
     */
    public function invoke(Invocation $invocation)
    {
        $invocation->setLastInsertId($this->value);
    }

    /**
     * @return  string
     */
    public function toString()
    {
        return 'set last insert ID';
    }
}
