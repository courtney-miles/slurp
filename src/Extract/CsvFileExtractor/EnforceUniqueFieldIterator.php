<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use IteratorIterator;
use MilesAsylum\Slurp\Extract\Exception\DuplicateFieldValueException;
use Traversable;

class EnforceUniqueFieldIterator extends IteratorIterator
{
    /**
     * @var array<string, array>
     */
    private $uniqueFieldValues;

    public function __construct(Traversable $iterator, array $uniqueFields)
    {
        parent::__construct($iterator);

        $this->uniqueFieldValues = array_fill_keys($uniqueFields, []);
    }

    public function current()
    {
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

        return parent::current();
    }
}
