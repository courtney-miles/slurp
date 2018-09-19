<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 8:56 PM
 */

namespace MilesAsylum\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\LoaderFactory;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Stage\InvokeExtractionPipeline;
use MilesAsylum\Slurp\Stage\LoadStage;
use MilesAsylum\Slurp\Stage\StageFactory;
use MilesAsylum\Slurp\Stage\StageInterface;
use MilesAsylum\Slurp\Stage\StageObserverInterface;
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
     * @var StageFactory
     */
    private $stageFactory;

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

    /**
     * @var StageObserverInterface[]
     */
    protected $allStageObservers = [];

    /**
     * @var StageObserverInterface[]
     */
    protected $extractionObservers = [];

    /**
     * @var StageObserverInterface[]
     */
    protected $validationObservers = [];

    /**
     * @var StageObserverInterface[]
     */
    protected $transformationObservers = [];
    /**
     * @var StageObserverInterface[]
     */
    protected $loadObservers = [];

    public function __construct(
        PipelineBuilder $innerPipelineBuilder,
        PipelineBuilder $outerPipelineBuilder,
        StageFactory $stageFactory
    ) {
        $this->innerPipelineBuilder = $innerPipelineBuilder;
        $this->outerPipelineBuilder = $outerPipelineBuilder;
        $this->stageFactory = $stageFactory;
    }

    public static function create(): self
    {
        return new static(
            new PipelineBuilder(),
            new PipelineBuilder(),
            new StageFactory()
        );
    }

    public function setTableSchema(Schema $tableSchema): self
    {
        $this->schemaValidator = new SchemaValidator($tableSchema);
        $this->schemaTransformer = new SchemaTransformer($tableSchema);

        return $this;
    }

    public function createTableSchemaFromPath(string $path): Schema
    {
        return new Schema($path);
    }

    public function createTableSchemaFromArray(array $arr): Schema
    {
        return new Schema($arr);
    }

    public function addValidationConstraint($field, Constraint $constraint): self
    {
        if (!isset($this->constraintValidator)) {
            $this->constraintValidator = new ConstraintValidator(
                Validation::createValidator()
            );
        }

        $this->constraintValidator->addColumnConstraints($field, $constraint);

        $this->validationStages[] = $this->stageFactory->createValidationStage($this->constraintValidator);

        return $this;
    }

    public function addTransformationChange($valueName, Change $change): self
    {
        if (!isset($this->transformer)) {
            $this->transformer = Transformer::createTransformer();
        }

        $this->transformer->setFieldChanges($valueName, $change);

        $this->transformationStages[] = $this->stageFactory->createTransformationStage($this->transformer);

        return $this;
    }

    public function addLoader(LoaderInterface $loader): self
    {
        $this->loadStages[] = $this->stageFactory->createLoadStage($loader);
        $this->postExtractionStages[] = $this->stageFactory->createFinaliseLoadStage($loader);

        return $this;
    }

    public function createDatabaseLoader(\PDO $pdo, string $table, array $fieldMappings, int $batchSize): DatabaseLoader
    {
        return new DatabaseLoader(
            $table,
            $fieldMappings,
            new LoaderFactory($pdo),
            $batchSize
        );
    }

    public function addAllStagesObserver(StageObserverInterface $observer): self
    {
        $this->allStageObservers[] = $observer;

        return $this;
    }

    public function addValidationObserver(StageObserverInterface $observer): self
    {
        $this->validationObservers[] = $observer;

        return $this;
    }

    public function addTransformationObserver(StageObserverInterface $observer): self
    {
        $this->transformationObservers[] = $observer;

        return $this;
    }

    public function addLoadObserver(StageObserverInterface $observer): self
    {
        $this->loadObservers[] = $observer;

        return $this;
    }

    public function build(): Slurp
    {
        if (isset($this->schemaValidator)) {
            $vs = $this->stageFactory->createValidationStage($this->schemaValidator);
            $this->attachValidationObservers($vs);
            $this->innerPipelineBuilder->add($vs);
        }

        if (isset($this->schemaTransformer)) {
            $ts = $this->stageFactory->createTransformationStage($this->schemaTransformer);
            $this->attachTransformationObservers($ts);
            $this->innerPipelineBuilder->add($ts);
        }

        foreach ($this->validationStages as $validationStage) {
            $this->attachValidationObservers($validationStage);
            $this->innerPipelineBuilder->add($validationStage);
        }

        foreach ($this->transformationStages as $transformationStage) {
            $this->attachTransformationObservers($transformationStage);
            $this->innerPipelineBuilder->add($transformationStage);
        }

        foreach ($this->loadStages as $loadStage) {
            $this->attachLoadObservers($loadStage);
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

    protected function attachValidationObservers(ValidationStage $validationStage)
    {
        foreach ($this->validationObservers as $observer) {
            $validationStage->attachObserver($observer);
        }

        $this->attachAllStageObservers($validationStage);
    }

    protected function attachTransformationObservers(TransformationStage $transformationStage)
    {
        foreach ($this->transformationObservers as $observer) {
            $transformationStage->attachObserver($observer);
        }

        $this->attachAllStageObservers($transformationStage);
    }

    protected function attachLoadObservers(LoadStage $loadStage)
    {
        foreach ($this->loadObservers as $observer) {
            $loadStage->attachObserver($observer);
        }

        $this->attachAllStageObservers($loadStage);
    }

    protected function attachAllStageObservers(StageInterface $stage)
    {
        foreach ($this->allStageObservers as $observer) {
            $stage->attachObserver($observer);
        }
    }
}
