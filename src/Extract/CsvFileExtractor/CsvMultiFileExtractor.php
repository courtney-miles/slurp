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

use MilesAsylum\Slurp\SlurpFactory;

class CsvMultiFileExtractor implements CsvFileExtractorInterface
{
    /**
     * @var CsvFileExtractor[]
     */
    protected $extractors = [];

    public function __construct(array $csvExtractors)
    {
        foreach ($csvExtractors as $extractor) {
            $this->addExtractor($extractor);
        }
    }

    /**
     * @deprecated
     * @see SlurpFactory::createCsvMultiFileExtractor()
     */
    public static function createFromPaths(array $paths)
    {
        $extractors = [];

        foreach ($paths as $path) {
            $extractors[] = CsvFileExtractor::createFromPath($path);
        }

        return new static($extractors);
    }

    public function setDelimiter(string $delimiter): void
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setDelimiter($delimiter);
        }
    }

    public function setEnclosure(string $enclosure): void
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setEnclosure($enclosure);
        }
    }

    public function setEscape(string $escape): void
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setEscape($escape);
        }
    }

    /**
     * Loads the first row in the CSV file as the headers.
     */
    public function loadHeadersFromFile(): void
    {
        foreach ($this->extractors as $extractor) {
            $extractor->loadHeadersFromFile();
        }
    }

    public function setHeaders(array $headers): void
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setHeaders($headers);
        }
    }

    public function getIterator(): \Traversable
    {
        $iterator = new \AppendIterator();

        foreach ($this->extractors as $extractor) {
            $iterator->append($extractor->getIterator());
        }

        return $iterator;
    }

    protected function addExtractor(CsvFileExtractor $extractor): void
    {
        $this->extractors[] = $extractor;
    }
}
