<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

/**
 * Invocation
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface Invocation
{
    /**
     * @return  string
     */
    function getQuery(): string;

    /**
     * @return  integer|NULL
     */
    function getAffectedRows(): ?int;

    /**
     * @param  integer  $count
     */
    function setAffectedRows(int $count): void;

    /**
     * @return  mixed|NULL
     */
    function getLastInsertId();

    /**
     * @param  mixed  $value
     */
    function setLastInsertId($value): void;

    /**
     * @return  iterable|NULL
     */
    function getResultSet(): ?iterable;

    /**
     * @param  iterable  $result
     */
    function setResultSet(iterable $result): void;
}
