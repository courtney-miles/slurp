<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 11:27 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\DateTimeFormat;
use MilesAsylum\Slurp\Transform\SlurpTransformer\DateTimeFormatTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Exception\MissingRequiredOptionException;
use PHPUnit\Framework\TestCase;

class DateTimeFormatTest extends TestCase
{
    public function testGetFormatFrom()
    {
        $this->assertSame(
            'Y-m-d',
            (new DateTimeFormat('Y-m-d', 'Y'))->getFromFormat()
        );
    }

    public function testGetFormatTo()
    {
        $this->assertSame(
            'Y-m-d',
            (new DateTimeFormat('Y', 'Y-m-d'))->getToFormat()
        );
    }

    public function testTransformedBy()
    {
        $this->assertSame(
            DateTimeFormatTransformer::class,
            (new DateTimeFormat('Y', 'Y'))->transformedBy()
        );
    }

    public function testCreateFromOptions()
    {
        $change = DateTimeFormat::createFromOptions(
            ['fromFormat' => 'Y-m-d', 'toFormat' => 'd-m-Y']
        );

        $this->assertSame('Y-m-d', $change->getFromFormat());
        $this->assertSame('d-m-Y', $change->getToFormat());
    }

    /**
     * @dataProvider getMissingRequiredOptionsTestData
     * @param array $options
     */
    public function testExceptionOnMissingRequiredOptions(array $options)
    {
        $this->expectException(MissingRequiredOptionException::class);

        DateTimeFormat::createFromOptions($options);
    }

    public function getMissingRequiredOptionsTestData()
    {
        return [
            [['fromFormat' => 'Y']],
            [['toFormat' => 'Y']],
        ];
    }
}
