<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 8:56 PM
 */

namespace MilesAsylum\Slurp;

use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Stage\LoadStage;
use MilesAsylum\Slurp\Stage\TransformationStage;
use MilesAsylum\Slurp\Stage\ValidationStage;
use MilesAsylum\Slurp\Transform\Change;
use MilesAsylum\Slurp\Transform\Transformer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SlurpBuilder
{
    /**
     * @var PipelineBuilder
     */
    private $pipelineBuilder;

    /**
     * @var ValidationStage[]
     */
    protected $validationStages = [];

    /**
     * @var TransformationStage[]
     */
    protected $transformationStages = [];

    /**
     * @var LoadStage[]
     */
    protected $loadStages = [];

    public function __construct(PipelineBuilder $pipelineBuilder)
    {
        $this->pipelineBuilder = $pipelineBuilder;
    }

    public function addConstraint($valueName, Constraint $constraint, ValidatorInterface $validator): self
    {
        $this->validationStages[] = new ValidationStage($valueName, $constraint, $validator);

        return $this;
    }

    public function addChange($valueName, Change $change, Transformer $transformer): self
    {
        $this->transformationStages[] = new TransformationStage($valueName, $change, $transformer);

        return $this;
    }

    public function addLoader(LoaderInterface $loader): self
    {
        $this->loadStages[] = new LoadStage($loader);

        return $this;
    }

    public function build()
    {
        foreach ($this->validationStages as $validationStage) {
            $this->pipelineBuilder->add($validationStage);
        }

        foreach ($this->transformationStages as $transformationStage) {
            $this->pipelineBuilder->add($transformationStage);
        }

        foreach ($this->loadStages as $loadStage) {
            $this->pipelineBuilder->add($loadStage);
        }

        return new Slurp($this->pipelineBuilder->build());
    }
}
