<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:27 PM
 */

namespace MilesAsylum\Slurp\Transform;

class Transformer implements TransformerInterface
{
    /**
     * @var TransformerLoader
     */
    private $loader;

    public function __construct(TransformerLoader $loader)
    {
        $this->loader = $loader;
    }

    public static function createTransformer()
    {
        return new self(new TransformerLoader());
    }

    public function transform($value, Change $change)
    {
        return $this->loader->loadTransformer($change)
            ->transform($value, $change);
    }
}
