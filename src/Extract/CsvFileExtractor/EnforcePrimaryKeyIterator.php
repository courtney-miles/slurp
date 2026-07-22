<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\Exception\DuplicatePrimaryKeyValueException;
use MilesAsylum\Slurp\Extract\Exception\MissingPrimaryKeyException;

class EnforcePrimaryKeyIterator extends \IteratorIterator
{
    private $primaryKeyFieldsValues = [];

    private $primaryKeyFields = [];

    private $lastKey;

    private $lastRecord;

    private $hasCache = false;

    public function __construct(\Traversable $iterator, array $primaryKeyFields)
    {
        parent::__construct($iterator);
        $this->primaryKeyFields = $primaryKeyFields;
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->primaryKeyFieldsValues = [];
        $this->hasCache = false;
        $this->lastKey = null;
        $this->lastRecord = null;
    }

    /**
     * @throws DuplicatePrimaryKeyValueException
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $key = $this->key();

        if ($this->hasCache && $key === $this->lastKey) {
            return $this->lastRecord;
        }

        $currentRecord = parent::current();
        $pkValues = [];

        foreach ($this->primaryKeyFields as $pkField) {
            if (!array_key_exists($pkField, $currentRecord)) {
                throw new MissingPrimaryKeyException('The supplied record does not contain the primary key field ' . $pkField . '.');
            }

            $pkValues[$pkField] = $currentRecord[$pkField];
        }

        $pkValues = array_values($pkValues);
        $normPkValues = self::normalisePrimaryKeysValue($pkValues);

        if (isset($this->primaryKeyFieldsValues[$normPkValues])) {
            throw DuplicatePrimaryKeyValueException::create($this->primaryKeyFields, $pkValues, $this->key());
        }

        $this->primaryKeyFieldsValues[$normPkValues] = true;

        $this->lastKey = $key;
        $this->lastRecord = $currentRecord;
        $this->hasCache = true;

        return $currentRecord;
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
