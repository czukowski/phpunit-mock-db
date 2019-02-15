<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB;

use Cz\PHPUnit\MockDB\MockObject\MockWrapper,
    PHPUnit\Framework\MockObject\MockObject,
    LogicException;

/**
 * MockTrait
 * 
 * @author   czukowski
 * @license  MIT License
 */
trait MockTrait
{
    /**
     * @return  Mock
     */
    public function createDatabaseMock()
    {
        $mock = new Mock;
        $this->registerMockObject(new MockWrapper($mock));
        $this->getDatabaseDriver()
            ->setMockObject($mock);
        return $mock;
    }

    /**
     * @return  DatabaseDriverInterface
     * @throws  LogicException
     */
    protected function getDatabaseDriver()
    {
        // Override this method to return a `DatabaseDriverInterface` instance.
        throw new LogicException('Missing implementation');
    }

    /**
     * @param  MockObject  $mockObject
     */
    abstract public function registerMockObject(MockObject $mockObject): void;
}
