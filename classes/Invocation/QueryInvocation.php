<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Invocation;

use Cz\PHPUnit\MockDB\Invocation;

/**
 * QueryInvocation
 * 
 * @author   czukowski
 * @license  MIT License
 */
class QueryInvocation implements Invocation
{
    /**
     * @var  integer
     */
    private $affectedRows;
    /**
     * @var  mixed
     */
    private $lastInsertId;
    /**
     * @var  string
     */
    private $query;
    /**
     * @var  mixed
     */
    private $resultSet;

    /**
     * @param  string  $query
     */
    public function __construct(string $query)
    {
        $this->query = $query;
    }

    /**
     * @return  string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return  integer
     */
    public function getAffectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * @param  integer  $count
     */
    public function setAffectedRows(int $count)
    {
        $this->affectedRows = $count;
    }

    /**
     * @return  mixed
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

    /**
     * @param  mixed  $value
     */
    public function setLastInsertId($value)
    {
        $this->lastInsertId = $value;
    }

    /**
     * @return  iterable
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * @param  iterable  $result
     */
    public function setResultSet($result)
    {
        $this->resultSet = $result;
    }
}
