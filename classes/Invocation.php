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
    function getAffectedRows();

    /**
     * @param  integer  $count
     */
    function setAffectedRows(int $count);

    /**
     * @return  mixed|NULL
     */
    function getLastInsertId();

    /**
     * @param  mixed  $value
     */
    function setLastInsertId($value);

    /**
     * @return  iterable|NULL
     */
    function getResultSet();

    /**
     * @param  iterable  $result
     */
    function setResultSet($result);
}
