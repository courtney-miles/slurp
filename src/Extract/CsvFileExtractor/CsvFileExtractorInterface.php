<?php
/**
 * Author: Courtney Miles
 * Date: 1/10/18
 * Time: 10:40 PM
 */

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
