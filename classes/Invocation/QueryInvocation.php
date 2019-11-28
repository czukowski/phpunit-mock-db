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
     * @var  array
     */
    private $parameters;
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
     * @param  array   $parameters
     */
    public function __construct($query, array $parameters = [])
    {
        $this->query = $query;
        $this->parameters = $parameters;
    }

    /**
     * @return  string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return  array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param  iterable  $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
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
