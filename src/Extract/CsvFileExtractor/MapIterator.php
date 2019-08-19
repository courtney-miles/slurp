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
