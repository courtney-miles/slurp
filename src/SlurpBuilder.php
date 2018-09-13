<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 8:56 PM
 */

namespace MilesAsylum\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Stage\FinaliseLoadStage;
use MilesAsylum\Slurp\Stage\InvokeExtractionPipeline;
use MilesAsylum\Slurp\Stage\LoadStage;
use MilesAsylum\Slurp\Stage\TransformationStage;
use MilesAsylum\Slurp\Stage\ValidationStage;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
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
     * @var SchemaValidator
     */
    protected $schemaValidator;

    /**
     * @var SchemaTransformer
     */
    protected $schemaTransformer;

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

    public static function create(): self
    {
        return new static(
            new PipelineBuilder(),
            new PipelineBuilder()
        );
    }

    public function setTableSchema(Schema $tableSchema): self
    {
        $this->schemaValidator = new SchemaValidator($tableSchema);
        $this->schemaTransformer = new SchemaTransformer($tableSchema);

        return $this;
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
        if (isset($this->schemaValidator)) {
            $this->innerPipelineBuilder->add(
                new ValidationStage($this->schemaValidator)
            );
        }

        if (isset($this->schemaTransformer)) {
            $this->innerPipelineBuilder->add(
                new TransformationStage($this->schemaTransformer)
            );
        }

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
