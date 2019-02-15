<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Stub;

use Cz\PHPUnit\MockDB\Invocation,
    Cz\PHPUnit\MockDB\Stub;

/**
 * ReturnResultSetStub
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ReturnResultSetStub implements Stub
{
    /**
     * @var  mixed
     */
    private $value;

    /**
     * @param  mixed  $resultSet
     */
    public function __construct($resultSet)
    {
        $this->value = $resultSet;
    }

    /**
     * @param  Invocation  $invocation
     */
    public function invoke(Invocation $invocation)
    {
        $invocation->setResultSet($this->value);
    }

    /**
     * @return  string
     */
    public function toString(): string
    {
        return 'return result set';
    }
}
