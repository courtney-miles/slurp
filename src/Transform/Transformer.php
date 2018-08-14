<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 5:37 PM
 */

namespace MilesAsylum\Slurp\Transform;


class Transformer
{
    /**
     * @var Transformation[][]
     */
    protected $columnTranslators = [];

    private $columns = [];

    private $colPositions = [];

    /**
     * @var TransformerLoader
     */
    private $loader;

    public function __construct(TransformerLoader $loader, array $columns)
    {
        $this->loader = $loader;
        $this->columns = array_values($columns);
        $this->colPositions = array_flip($this->columns);
    }

    /**
     * @param $columnName
     * @param Transformation|Transformation[] $transformations
     */
    public function addColumnTransformations($columnName, $transformations)
    {
        if (!is_array($transformations)) {
            $transformations = [$transformations];
        }

        $this->columnTranslators[$columnName] = $transformations;
    }

    public function transformRow($row)
    {
        foreach ($this->columnTranslators as $col => $translators) {
            foreach ($translators as $translator) {
                $this->setCellValue(
                    $row,
                    $col,
                    $this->loader->loadTransformer($translator)->transform(
                        $this->getCellValue($row, $col),
                        $translator
                    )
                );
            }
        }

        return $row;
    }

    protected function getCellValue($row, $column)
    {
        return $row[$this->colPositions[$column]];
    }

    protected function setCellValue(&$row, $column, $value)
    {
        $row[$this->colPositions[$column]] = $value;
    }
}