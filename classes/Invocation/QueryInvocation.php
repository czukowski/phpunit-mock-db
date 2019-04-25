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
     * @var  string
     */
    private $query;
    /**
     * @var  iterable|NULL
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
