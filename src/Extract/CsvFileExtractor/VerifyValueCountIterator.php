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

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use IteratorIterator;
use MilesAsylum\Slurp\Extract\Exception\ValueCountMismatchException;
use Traversable;

class VerifyValueCountIterator extends IteratorIterator
{
    private $expectedValueCount;

    public function __construct(Traversable $iterator, int $expectedValueCount)
    {
        parent::__construct($iterator);
        $this->expectedValueCount = $expectedValueCount;
    }

    /**
     * @return array
     * @throws ValueCountMismatchException
     */
    public function current(): array
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
