<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use IteratorIterator;
use MilesAsylum\Slurp\Extract\Exception\DuplicatePrimaryKeyValueException;

class EnforcePrimaryKeyIterator extends IteratorIterator
{
    private $primaryKeyFieldsValues = [];

    private $primaryKeyFields = [];

    public function __construct(\Traversable $iterator, array $primaryKeyFields)
    {
        parent::__construct($iterator);
        $this->primaryKeyFields = $primaryKeyFields;
    }

    /**
     * @throws DuplicatePrimaryKeyValueException
     */
    public function current()
    {
        $currentRecord = parent::current();
        $pkValues = [];

        foreach ($this->primaryKeyFields as $pkField) {
            if (!array_key_exists($pkField, $currentRecord)) {
                throw new \InvalidArgumentException('The supplied record does not contain the primary key field ' . $pkField . '.');
            }

            $pkValues[$pkField] = $currentRecord[$pkField];
        }

        $pkValues = array_values($pkValues);

        if (in_array($pkValues, $this->primaryKeyFieldsValues, false)) {
            throw DuplicatePrimaryKeyValueException::create($this->primaryKeyFields, $pkValues, $this->key());
        }

        $this->primaryKeyFieldsValues[] = $pkValues;

        return parent::current();
    }
}
