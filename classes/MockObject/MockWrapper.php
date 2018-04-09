<?php
namespace Cz\PHPUnit\MockDB\MockObject;

use Cz\PHPUnit\MockDB\InvocationMocker,
    Cz\PHPUnit\MockDB\Mock,
    PHPUnit_Framework_ExpectationFailedException as ExpectationFailedException,
    PHPUnit_Framework_MockObject_Matcher_Invocation as Invocation,
    PHPUnit_Framework_MockObject_MockObject as MockObject,
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
     */
    public function __phpunit_verify()
    {
        $this->object->verify();
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
     * @param  Invocation  $matcher
     */
    public function expects(Invocation $matcher)
    {
        return $this->object->expects($matcher);
    }
}
