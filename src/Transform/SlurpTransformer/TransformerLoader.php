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

class TransformerLoader
{
    /**
     * @var ChangeTransformerInterface[]
     */
    private $loadedTransformers = [];

    /**
     * @param Change $change
     *
     * @return ChangeTransformerInterface
     */
    public function loadTransformer(Change $change): ChangeTransformerInterface
    {
        if (!isset($this->loadedTransformers[$change->transformedBy()])) {
            $transformedBy = $change->transformedBy();
            $this->loadedTransformers[$transformedBy] = new $transformedBy();
        }

        return $this->loadedTransformers[$change->transformedBy()];
    }
}
