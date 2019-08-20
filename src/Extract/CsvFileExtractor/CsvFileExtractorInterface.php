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

use MilesAsylum\Slurp\Extract\ExtractorInterface;

interface CsvFileExtractorInterface extends ExtractorInterface
{
    public function setDelimiter(string $delimiter): void;

    public function setEnclosure(string $enclosure): void;

    public function setEscape(string $escape): void;

    public function loadHeadersFromFile(): void;

    public function setHeaders(array $headers): void;
}
