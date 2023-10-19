<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\Exception\DuplicatePrimaryKeyValueException;

class EnforcePrimaryKeyIterator extends \IteratorIterator
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
    #[\ReturnTypeWillChange]
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
        $normPkValues = self::normalisePrimaryKeysValue($pkValues);

        if (isset($this->primaryKeyFieldsValues[$normPkValues])) {
            throw DuplicatePrimaryKeyValueException::create($this->primaryKeyFields, $pkValues, $this->key());
        }

        $this->primaryKeyFieldsValues[$normPkValues] = true;

        return parent::current();
    }

    private static function normalisePrimaryKeysValue(array $primaryKeyValues): string
    {
        return implode(
            ':',
            array_map(
                static function ($value) {
                    return addcslashes((string) $value, '\:');
                },
                $primaryKeyValues
            )
        );
    }
}
