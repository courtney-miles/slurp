<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\Exception\DuplicateFieldValueException;

class EnforceUniqueFieldIterator extends \IteratorIterator
{
    /**
     * @var array<string, array>
     */
    private $uniqueFieldValues;

    private $lastKey;

    private $lastRecord;

    private $hasCache = false;

    public function __construct(\Traversable $iterator, array $uniqueFields)
    {
        parent::__construct($iterator);

        $this->uniqueFieldValues = array_fill_keys($uniqueFields, []);
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->uniqueFieldValues = array_fill_keys(array_keys($this->uniqueFieldValues), []);
        $this->hasCache = false;
        $this->lastKey = null;
        $this->lastRecord = null;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        $key = $this->key();

        if ($this->hasCache && $key === $this->lastKey) {
            return $this->lastRecord;
        }

        $currentRecord = parent::current();

        foreach ($currentRecord as $field => $value) {
            if (!array_key_exists($field, $this->uniqueFieldValues)) {
                continue;
            }

            if (isset($this->uniqueFieldValues[$field][$value])) {
                throw DuplicateFieldValueException::create($field, $value, $this->key());
            }

            $this->uniqueFieldValues[$field][$value] = true;
        }

        $this->lastKey = $key;
        $this->lastRecord = $currentRecord;
        $this->hasCache = true;

        return $currentRecord;
    }
}
