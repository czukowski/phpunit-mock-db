<?php
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
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * @return  string
     */
    public function getQuery()
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
    public function setAffectedRows($count)
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
     * @return  array
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * @param  mixed  $result
     */
    public function setResultSet($result)
    {
        $this->resultSet = $result;
    }
}
