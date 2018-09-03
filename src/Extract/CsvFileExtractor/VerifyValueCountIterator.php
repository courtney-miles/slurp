<?php
/**
 * Author: Courtney Miles
 * Date: 27/08/18
 * Time: 6:05 PM
 */

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\Exception\ValueCountMismatchException;
use Traversable;

class VerifyValueCountIterator extends \IteratorIterator
{
    private $expectedValueCount;

    public function __construct(Traversable $iterator, int $expectedValueCount)
    {
        parent::__construct($iterator);
        $this->expectedValueCount = $expectedValueCount;
    }

    /**
     * {@inheritdoc}
     * @throws ValueCountMismatchException
     */
    public function current()
    {
        $record = parent::current();

        if (count($record) !== $this->expectedValueCount) {
            $recordId = $this->key();
            throw ValueCountMismatchException::createMismatch(
                $recordId,
                count($record),
                $this->expectedValueCount
            );
        }

        return $record;
    }
}
