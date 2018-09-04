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
use MilesAsylum\Slurp\Stage\FinaliseLoadStage;
use MilesAsylum\Slurp\Stage\InvokeExtractionPipeline;
use MilesAsylum\Slurp\Stage\LoadStage;
use MilesAsylum\Slurp\Stage\TransformationStage;
use MilesAsylum\Slurp\Stage\ValidationStage;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\Transformer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SlurpBuilder
{
    /**
     * @var PipelineBuilder
     */
    private $outerPipelineBuilder;

    /**
     * @var PipelineBuilder
     */
    private $innerPipelineBuilder;

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

    protected $preExtractionStages = [];
    protected $postExtractionStages = [];

    public function __construct(PipelineBuilder $innerPipelineBuilder, PipelineBuilder $outerPipelineBuilder)
    {
        $this->innerPipelineBuilder = $innerPipelineBuilder;
        $this->outerPipelineBuilder = $outerPipelineBuilder;
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
        $this->postExtractionStages[] = new FinaliseLoadStage($loader);

        return $this;
    }

    public function build()
    {
        foreach ($this->validationStages as $validationStage) {
            $this->innerPipelineBuilder->add($validationStage);
        }

        foreach ($this->transformationStages as $transformationStage) {
            $this->innerPipelineBuilder->add($transformationStage);
        }

        foreach ($this->loadStages as $loadStage) {
            $this->innerPipelineBuilder->add($loadStage);
        }

        $this->outerPipelineBuilder->add(
            new InvokeExtractionPipeline($this->innerPipelineBuilder->build())
        );

        foreach ($this->postExtractionStages as $postExtractionStage) {
            $this->outerPipelineBuilder->add($postExtractionStage);
        }

        return new Slurp($this->outerPipelineBuilder->build());
    }
}
