<?php
namespace Cz\PHPUnit\MockDB\Invocation;

use InvalidArgumentException;

/**
 * KeywordBasedQueryInvocationFactory
 * 
 * Naive heuristics to prepare fallback invocation results based on SQL query first keyword.
 * 
 * @author   czukowski
 * @license  MIT License
 */
class KeywordBasedQueryInvocationFactory implements QueryInvocationFactoryInterface
{
    /**
     * @param   string  $sql
     * @return  QueryInvocation
     * @throws  InvalidArgumentException
     */
    public function createInvocation(string $sql): QueryInvocation
    {
        $trimmed = trim($sql);
        $keyword = $this->getKeyword($trimmed);
        if ($keyword) {
            $invocation = new QueryInvocation($trimmed);
            if (in_array($keyword, ['UPDATE', 'DELETE'], TRUE)) {
                $invocation->setAffectedRows(0);
            }
            elseif (in_array($keyword, ['INSERT', 'REPLACE'], TRUE)) {
                $invocation->setAffectedRows(0);
                $invocation->setLastInsertId(1);
            }
            elseif (in_array($keyword, ['SELECT', 'SHOW', 'EXEC', 'EXECUTE'], TRUE)) {
                $invocation->setResultSet([]);
            }
            return $invocation;
        }
        throw new InvalidArgumentException('SQL command not determined');
    }

    /**
     * @param   string  $sql
     * @return  string
     */
    protected function getKeyword(string $sql): string
    {
        list ($keyword, ) = preg_split('#\s+#', $sql, 2);
        return $keyword;
    }
}
