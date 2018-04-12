<?php
namespace Cz\PHPUnit\MockDB\Invocation;

/**
 * QueryInvocationFactoryInterface
 * 
 * Creates `QueryInvocation` instance and presets default (empty) result set, last insert
 * ID and affected rows count in case queries does not match any expected query.
 * 
 * Only useful when Mock object's 'require match' is set to FALSE, otherwise exception is
 * thrown on unexpected queries.
 * 
 * @author   czukowski
 * @license  MIT License
 */
interface QueryInvocationFactoryInterface
{
    /**
     * @param   string  $sql
     * @return  QueryInvocation
     */
    function createInvocation($sql);
}
