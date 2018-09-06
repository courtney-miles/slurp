<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 8:56 PM
 */

namespace MilesAsylum\Slurp;

use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Stage\FinaliseLoadStage;
use MilesAsylum\Slurp\Stage\InvokeExtractionPipeline;
use MilesAsylum\Slurp\Stage\LoadStage;
use MilesAsylum\Slurp\Stage\TransformationStage;
use MilesAsylum\Slurp\Stage\ValidationStage;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\TransformerLoader;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;

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

    /**
     * @var ConstraintValidator
     */
    protected $constraintValidator;

    /**
     * @var Transformer
     */
    protected $transformer;

    public function __construct(PipelineBuilder $innerPipelineBuilder, PipelineBuilder $outerPipelineBuilder)
    {
        $this->innerPipelineBuilder = $innerPipelineBuilder;
        $this->outerPipelineBuilder = $outerPipelineBuilder;
    }

    public function addConstraint($field, Constraint $constraint): self
    {
        if (!isset($this->constraintValidator)) {
            $this->constraintValidator = new ConstraintValidator(
                Validation::createValidator()
            );
        }

        $this->constraintValidator->addColumnConstraints($field, $constraint);

        $this->validationStages[] = new ValidationStage($this->constraintValidator);

        return $this;
    }

    public function addChange($valueName, Change $change): self
    {
        if (!isset($this->transformer)) {
            $this->transformer = Transformer::createTransformer();
        }

        $this->transformer->setFieldChanges($valueName, $change);

        $this->transformationStages[] = new TransformationStage($this->transformer);

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
