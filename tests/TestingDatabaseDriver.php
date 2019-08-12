<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use LogicException;

/**
 * A super-simple implementation for a fake database driver.
 */
class TestingDatabaseDriver implements DatabaseDriverInterface
{
    /**
     * @var  Mock
     */
    private $mock;

    /**
     * @param   Mock  $mock
     * @throws  LogicException
     */
    public function setMockObject(Mock $mock): void
    {
        if ($this->mock !== NULL) {
            throw new LogicException('Mock already set');
        }
        $this->mock = $mock;
    }

    /**
     * @param   string  $query
     * @return  mixed
     */
    public function query($query)
    {
        $invocation = $this->mock->invoke($query);
        if (strpos($query, 'SELECT') === 0) {
            return $invocation->getResultSet();
        }
        elseif (strpos($query, 'INSERT') === 0) {
            return $invocation->getLastInsertId();
        }
        elseif (strpos($query, 'UPDATE') === 0 || strpos($query, 'DELETE') === 0) {
            return $invocation->getAffectedRows();
        }
    }
}
