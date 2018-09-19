<?php
/**
 * Author: Courtney Miles
 * Date: 16/09/18
 * Time: 9:13 AM
 */

namespace MilesAsylum\Slurp\Stage;

use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use MilesAsylum\Slurp\Validate\ValidatorInterface;

class StageFactory
{
    public function createValidationStage(ValidatorInterface $validator): ValidationStage
    {
        return new ValidationStage($validator);
    }

    public function createTransformationStage(TransformerInterface $transformer): TransformationStage
    {
        return new TransformationStage($transformer);
    }

    public function createLoadStage(LoaderInterface $loader): LoadStage
    {
        return new LoadStage($loader);
    }

    public function createFinaliseLoadStage(LoaderInterface $loader): FinaliseLoadStage
    {
        return new FinaliseLoadStage($loader);
    }
}