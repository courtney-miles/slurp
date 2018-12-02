<?php
/**
 * Author: Courtney Miles
 * Date: 1/12/18
 * Time: 7:53 PM
 */

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
