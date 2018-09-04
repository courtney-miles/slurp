<?php
/**
 * Author: Courtney Miles
 * Date: 14/08/18
 * Time: 9:57 PM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\ChangeTransformerInterface;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;

class TransformerLoader
{
    /**
     * @var ChangeTransformerInterface[]
     */
    private $loadedTransformers = [];

    /**
     * @param Change $change
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
