<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\InvocationMocker,
    Cz\PHPUnit\MockDB\Mock,
    PHPUnit\Framework\ExpectationFailedException,
    PHPUnit\Framework\MockObject\Matcher\Invocation,
    PHPUnit\Framework\MockObject\MockObject,
    LogicException;

/**
 * MockWrapper
 * 
 * This class purpose is to be injected into a test case in order to verify expectations.
 * Only `__phpunit_verify` and `__phpunit_hasMatchers` methods are important for this.
 * A few other simple methods are implemented as well and others are throwing `LogicException`
 * in case they are called.
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
    public function __phpunit_hasMatchers()
    {
        return $this->__phpunit_getInvocationMocker()
            ->hasMatchers();
    }

    /**
     * @throws  ExpectationFailedException
     * @throws  LogicException
     */
    public function __phpunit_verify(bool $unsetInvocationMocker = TRUE)
    {
        $this->object->verify();
        if ($unsetInvocationMocker) {
            $this->object->unsetInvocationMocker();
        }
    }

    /**
     * @return  InvocationMocker
     */
    public function __phpunit_getInvocationMocker()
    {
        return $this->object->getInvocationMocker();
    }

    /**
     * @throws  LogicException
     */
    public function __phpunit_setOriginalObject($originalObject)
    {
        throw new LogicException('Not supported');
    }

    /**
     * @throws  LogicException
     */
    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration)
    {
        throw new LogicException('Not supported');
    }

    /**
     * @param  Invocation  $matcher
     */
    public function expects(Invocation $matcher)
    {
        return $this->object->expects($matcher);
    }
}
