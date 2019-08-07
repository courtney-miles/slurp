<?php
/**
 * Author: Courtney Miles
 * Date: 27/08/18
 * Time: 6:32 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use IteratorIterator;
use Traversable;

class MapIterator extends IteratorIterator
{
    /**
     * @var array
     */
    private $headers;

    public function __construct(Traversable $iterator, array $headers)
    {
        parent::__construct($iterator);
        $this->headers = $headers;
    }

    public function current()
    {
        $record = parent::current();

        return array_combine($this->headers, $record);
    }
}
