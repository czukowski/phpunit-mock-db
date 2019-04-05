<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\Mock,
    PHPUnit\Framework\ExpectationFailedException,
    PHPUnit\Framework\MockObject\Builder\InvocationMocker as BuilderInvocationMocker,
    PHPUnit\Framework\MockObject\Matcher\Invocation,
    PHPUnit\Framework\MockObject\MockObject,
    PHPUnit\Framework\MockObject\InvocationMocker,
    LogicException;

/**
 * MockWrapper
 * 
 * This class only purpose is to be injected into a test case in order to verify expectations.
 * Only `__phpunit_verify` and `__phpunit_hasMatchers` methods are important for this.
 * All other methods will throw `LogicException` in case they are called.
 * 
 * @author   czukowski
 * @license  MIT License
 */
class MockWrapper implements MockObject
{
    /**
     * @var  Mock
     */
    private $object;

    /**
     * @param  Mock  $object
     */
    public function __construct(Mock $object)
    {
        $this->object = $object;
    }

    /**
     * @return  boolean
     */
    public function __phpunit_hasMatchers(): bool
    {
        return $this->object->getInvocationMocker()
            ->hasMatchers();
    }

    /**
     * @throws  ExpectationFailedException
     * @throws  LogicException
     */
    public function __phpunit_verify(bool $unsetInvocationMocker = TRUE): void
    {
        $this->object->verify();
        if ($unsetInvocationMocker) {
            $this->object->unsetInvocationMocker();
        }
    }

    /**
     * @throws  LogicException
     */
    public function __phpunit_getInvocationMocker(): InvocationMocker
    {
        throw new LogicException('Not supported');
    }

    /**
     * @throws  LogicException
     */
    public function __phpunit_setOriginalObject($originalObject): void
    {
        throw new LogicException('Not supported');
    }

    /**
     * @throws  LogicException
     */
    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration): void
    {
        throw new LogicException('Not supported');
    }

    /**
     * @param  Invocation  $matcher
     */
    public function expects(Invocation $matcher): BuilderInvocationMocker
    {
        throw new LogicException('Not supported');
    }
}
