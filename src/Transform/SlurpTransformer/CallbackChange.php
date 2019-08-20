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

class CallbackChange extends Change
{
    /**
     * @var callable
     */
    private $changeCallback;

    public function __construct(callable $changeCallback)
    {
        $this->changeCallback = $changeCallback;
    }

    /**
     * @return string
     */
    public function transformedBy(): string
    {
        return CallbackTransformer::class;
    }

    public function __invoke($value)
    {
        return ($this->changeCallback)($value);
    }
}
