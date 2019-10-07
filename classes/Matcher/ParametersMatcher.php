<?php declare(strict_types=1);

namespace Cz\PHPUnit\MockDB\Matcher;

use Cz\PHPUnit\MockDB\Invocation as BaseInvocation,
    PHPUnit\Framework\Constraint\Constraint;

/**
 * ParametersMatcher
 * 
 * @author   czukowski
 * @license  MIT License
 */
class ParametersMatcher
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
            $this->parameters[$index] = $parameter instanceof Constraint ? $parameter : new IsEqual($parameter);
        }
    }

    /**
     * @return  string
     */
    public function toString(): string
    {
        $text = 'with parameter';
        foreach ($this->parameters as $index => $parameter) {
            if ($index > 0) {
                $text .= ' and';
            }
            $text .= ' '.$index.' '.$parameter->toString();
        }
        return $text;
    }

    /**
     * @param   BaseInvocation  $invocation
     * @return  boolean
     * @throws  ExpectationFailedException
     */
    public function matches(BaseInvocation $invocation): bool
    {
        if (count($invocation->getParameters()) < count($this->parameters)) {
            throw new ExpectationFailedException(
                sprintf('Parameter count for invocation %s is too low.', $invocation->toString())
            );
        }

        foreach ($this->parameters as $index => $parameter) {
            $parameter->evaluate(
                $invocation->getParameters()[$index],
                sprintf(
                    'Parameter %s for invocation %s does not match expected value.',
                    $index,
                    $invocation->toString()
                )
            );
        }

        return TRUE;
    }
}
