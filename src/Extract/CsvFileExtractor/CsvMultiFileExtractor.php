<?php
/**
 * Author: Courtney Miles
 * Date: 1/10/18
 * Time: 7:58 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

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
    public function loadHeadersFromFile() : void
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

    public function getIterator()
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
