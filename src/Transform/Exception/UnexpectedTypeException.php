<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 11:06 PM
 */

namespace MilesAsylum\Slurp\Transform\Exception;

class UnexpectedTypeException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct($value, string $expectedType)
    {
        parent::__construct(sprintf('Expected argument of type "%s", "%s" given', $expectedType, \is_object($value) ? \get_class($value) : \gettype($value)));
    }
}