<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

abstract class AbstractChangeTransformer implements ChangeTransformerInterface
{
    final public function __construct()
    {
    }

    public function isString($value): bool
    {
        return is_scalar($value) || (is_object($value) && method_exists($value, '__toString'));
    }
}
