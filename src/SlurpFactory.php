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

namespace MilesAsylum\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Exception\FactoryException;
use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use MilesAsylum\Slurp\Filter\FilterInterface;
use MilesAsylum\Slurp\InnerPipeline\FiltrationStage;
use MilesAsylum\Slurp\InnerPipeline\InnerProcessor;
use MilesAsylum\Slurp\InnerPipeline\LoadStage;
use MilesAsylum\Slurp\InnerPipeline\TransformationStage;
use MilesAsylum\Slurp\InnerPipeline\ValidationStage;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\DmlStmtInterface;
use MilesAsylum\Slurp\Load\DatabaseLoader\LoaderFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\SimpleDeleteStmt;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterPipeline\ExtractionStage;
use MilesAsylum\Slurp\OuterPipeline\FinaliseStage;
use MilesAsylum\Slurp\OuterPipeline\OuterProcessor;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use PDO;
use Symfony\Component\Validator\Validation;
use Throwable;

class SlurpFactory
{
    public function createValidationStage(ValidatorInterface $validator): ValidationStage
    {
        return new ValidationStage($validator);
    }

    public function createTransformationStage(TransformerInterface $transformer): TransformationStage
    {
        return new TransformationStage($transformer);
    }

    public function createFiltrationStage(FilterInterface $filter): FiltrationStage
    {
        return new FiltrationStage($filter);
    }

    public function createLoadStage(LoaderInterface $loader): LoadStage
    {
        return new LoadStage($loader);
    }

    public function createEltFinaliseStage(LoaderInterface $loader): FinaliseStage
    {
        return new FinaliseStage($loader);
    }

    /**
     * @throws FactoryException
     */
    public function createTableSchemaFromPath(string $path): Schema
    {
        try {
            return new Schema($path);
        } catch (Throwable $e) {
            throw new FactoryException('Error creating table schema from file path: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws FactoryException
     */
    public function createTableSchemaFromArray(array $arr): Schema
    {
        try {
            return new Schema($arr);
        } catch (Throwable $e) {
            throw new FactoryException('Error creating table schema from array: ' . $e->getMessage(), 0, $e);
        }
    }

    public function createConstraintValidator(): ConstraintValidator
    {
        return new ConstraintValidator(
            Validation::createValidator()
        );
    }

    public function createConstraintFilter(): ConstraintFilter
    {
        return new ConstraintFilter(
            Validation::createValidator()
        );
    }

    public function createSchemaValidator(Schema $tableSchema): SchemaValidator
    {
        return new SchemaValidator($tableSchema);
    }

    public function createTransformer(): Transformer
    {
        return Transformer::createTransformer();
    }

    public function createSchemaTransformer(Schema $tableSchema): SchemaTransformer
    {
        return new SchemaTransformer($tableSchema);
    }

    /**
     * @param array $fieldMappings array key is the destination column and the array value is the source column
     */
    public function createDatabaseLoader(
        PDO $pdo,
        string $table,
        array $fieldMappings,
        int $batchSize = 100,
        DmlStmtInterface $preCommitStmt = null,
        string $database = null
    ): DatabaseLoader {
        return new DatabaseLoader(
            $table,
            $fieldMappings,
            new LoaderFactory($pdo),
            $batchSize,
            $preCommitStmt,
            $database
        );
    }

    public function createExtractionStage(
        PipelineInterface $innerPipeline,
        callable $interrupt = null
    ): ExtractionStage {
        return new ExtractionStage($innerPipeline, $interrupt);
    }

    public function createSlurp(PipelineInterface $pipeline): Slurp
    {
        return new Slurp($pipeline);
    }

    public function createInnerProcessor(): InnerProcessor
    {
        return new InnerProcessor();
    }

    public function createOuterProcessor(): OuterProcessor
    {
        return new OuterProcessor();
    }

    public function createSimpleDeleteStmt(
        PDO $pdo,
        string $table,
        array $conditions = [],
        string $database = null
    ): SimpleDeleteStmt {
        return new SimpleDeleteStmt($pdo, $table, $conditions, $database);
    }
}
