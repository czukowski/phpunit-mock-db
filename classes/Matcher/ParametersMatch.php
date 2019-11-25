<?php
namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    PHPUnit\Framework\Constraint\Constraint,
    PHPUnit\Framework\Constraint\IsAnything,
    PHPUnit\Framework\Constraint\IsIdentical,
    PHPUnit\Framework\ExpectationFailedException;

/**
 * ParametersMatch
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ParametersMatch implements ParametersMatcher
{
    /**
     * @var  array  Constraint[]
     */
    private $parameters = [];

    /**
     * @param  array  $parameters
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $index => $parameter) {
            $this->parameters[$index] = $parameter instanceof Constraint ? $parameter : new IsIdentical($parameter);
        }
    }

    /**
     * @return  string
     */
    public function toString(): string
    {
        $text = 'with parameter';
        foreach ($this->parameters as $index => $parameter) {
            if ($index > 0 && $index === count($this->parameters) - 1) {
                $text .= ' and parameter';
            }
            elseif ($index > 0) {
                $text .= ', parameter';
            }
            $text .= sprintf(' %d %s', $index + 1, $parameter->toString());
        }
        return $text;
    }

    public function matches(BaseInvocation $invocation)
    {
        $actual = $invocation->getParameters();

        if (count($actual) < count($this->parameters)) {
            $message = sprintf('Parameter count for invocation of `%s` is too low.', $invocation->getQuery());
            if (count($this->parameters) === 1 && $this->parameters[0] instanceof IsAnything) {
                $message .= "\nTo allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.";
            }
            throw new ExpectationFailedException($message);
        }

        foreach ($this->parameters as $index => $parameter) {
            $result = $parameter->evaluate(
                $actual[$index],
                sprintf(
                    'Parameter %s for invocation of `%s` does not match expected value.',
                    $index + 1,
                    $invocation->getQuery()
                ),
                TRUE
            );

            if ($result !== TRUE) {
                return $result;
            }
        }

        return TRUE;
    }

    /**
     * @param  BaseInvocation  $invocation
     */
    public function invoked(BaseInvocation $invocation)
    {}

    public function verify()
    {}
}
