<?php
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
    function getQuery();

    /**
     * @return  integer|NULL
     */
    function getAffectedRows();

    /**
     * @param  integer  $count
     */
    function setAffectedRows($count);

    /**
     * @return  mixed|NULL
     */
    function getLastInsertId();

    /**
     * @param  mixed  $value
     */
    function setLastInsertId($value);

    /**
     * @return  array|NULL
     */
    function getResultSet();

    /**
     * @param  mixed  $result
     */
    function setResultSet($result);
}
