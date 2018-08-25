<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 7:19 PM
 */

namespace MilesAsylum\Slurp\Validate;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Constraint[][];
     */
    private $columnConstraints = [];

    private $columns = [];

    private $colPositions = [];

    public function __construct(ValidatorInterface $validator, array $columns)
    {
        $this->validator = $validator;
        $this->columns = array_values($columns);
        $this->colPositions = array_flip($this->columns);
    }

    /**
     * @param $columnName
     * @param Constraint|Constraint[] $constraints
     */
    public function addColumnConstraints($columnName, $constraints)
    {
        $this->columnConstraints[$columnName] = $constraints;
    }

    /**
     * @param $values
     * @param $rowId
     * @return ConstraintViolationListInterface
     */
    public function validateValues(array $values, $rowId = null)
    {
        $vContext = $this->validator
            ->startContext($rowId);

        foreach ($this->columnConstraints as $col => $constraints) {
            $vContext->atPath($col)
                ->validate($this->getCellValue($values, $col), $constraints);
        }

        return $vContext->getViolations();
    }

    protected function getCellValue($row, $column)
    {
        return $row[$this->colPositions[$column]];
    }
}
