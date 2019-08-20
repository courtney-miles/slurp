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

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\CsvFileExtractor;

use ArrayIterator;
use MilesAsylum\Slurp\Extract\CsvFileExtractor\MapIterator;
use PHPUnit\Framework\TestCase;

class MapIteratorTest extends TestCase
{
    public function testMapHeaders(): void
    {
        $mi = new MapIterator(
            new ArrayIterator([[123, 234]]),
            ['val_1', 'val_2']
        );

        $mi->rewind();

        $this->assertSame(
            ['val_1' => 123, 'val_2' => 234],
            $mi->current()
        );
    }
}
