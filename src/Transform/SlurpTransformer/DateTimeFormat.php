<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 11:22 AM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\Exception\MissingRequiredOptionException;

class DateTimeFormat extends Change
{
    private $fromFormat;

    private $toFormat;

    public function __construct($fromFormat, $toFormat)
    {
        $this->fromFormat = $fromFormat;
        $this->toFormat = $toFormat;
    }

    public static function createFromOptions(array $options = []): self
    {
        $reqOptions = ['fromFormat', 'toFormat'];

        if ($missingOptions = array_diff($reqOptions, array_keys($options))) {
            throw MissingRequiredOptionException::createMissingOptions(self::class, $missingOptions);
        }

        return new self($options['fromFormat'], $options['toFormat']);
    }

    /**
     * @return string
     */
    public function getFromFormat(): string
    {
        return $this->fromFormat;
    }

    /**
     * @return string
     */
    public function getToFormat(): string
    {
        return $this->toFormat;
    }

    /**
     * @return string
     */
    public function transformedBy(): string
    {
        return DateTimeFormatTransformer::class;
    }
}
