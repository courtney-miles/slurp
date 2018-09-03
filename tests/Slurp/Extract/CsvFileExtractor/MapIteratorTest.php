<?php
/**
 * Author: Courtney Miles
 * Date: 27/08/18
 * Time: 6:47 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\CsvFileExtractor\MapIterator;
use PHPUnit\Framework\TestCase;

class MapIteratorTest extends TestCase
{
    public function testMapHeaders()
    {
        $mi = new MapIterator(
            new \ArrayIterator([[123, 234]]),
            ['val_1', 'val_2']
        );

        $mi->rewind();

        $this->assertSame(
            ['val_1' => 123, 'val_2' => 234],
            $mi->current()
        );
    }
}
