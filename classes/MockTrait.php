<?php
namespace Cz\PHPUnit\MockDB;

use LogicException;

/**
 * MockTrait
 * 
 * @author   czukowski
 * @license  MIT License
 */
trait MockTrait
{
    /**
     * @var  array  Mock[]
     */
    private $mockObjects = [];

    /**
     * @return  Mock
     */
    public function createDatabaseMock()
    {
        $mock = new Mock;
        $this->mockObjects[] = $mock;
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
     * Reset registered mock objects after running test.
     */
    public function runBare()
    {
        parent::runBare();
        $this->mockObjects = [];
    }

    /**
     * Verify DB mock objects.
     */
    protected function verifyMockObjects()
    {
        parent::verifyMockObjects();
        foreach ($this->mockObjects as $mockObject) {
            if ($mockObject->getInvocationMocker()->hasMatchers()) {
                $this->addToAssertionCount(1);
            }
            $mockObject->verify();
        }
    }
}
