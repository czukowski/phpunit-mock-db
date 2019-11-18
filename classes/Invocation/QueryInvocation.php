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
     * @var  integer|NULL
     */
    private $affectedRows;
    /**
     * @var  mixed|NULL
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
     * @var  iterable|NULL
     */
    private $resultSet;

    /**
     * @param  string  $query
     * @param  array   $parameters
     */
    public function __construct(string $query, array $parameters = [])
    {
        $this->query = $query;
        $this->parameters = $parameters;
    }

    /**
     * @return  string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return  array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param  iterable  $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return  integer|NULL
     */
    public function getAffectedRows(): ?int
    {
        return $this->affectedRows;
    }

    /**
     * @param  integer  $count
     */
    public function setAffectedRows(int $count): void
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
    public function setLastInsertId($value): void
    {
        $this->lastInsertId = $value;
    }

    /**
     * @return  iterable|NULL
     */
    public function getResultSet(): ?iterable
    {
        return $this->resultSet;
    }

    /**
     * @param  iterable  $result
     */
    public function setResultSet(iterable $result): void
    {
        $this->resultSet = $result;
    }
}
