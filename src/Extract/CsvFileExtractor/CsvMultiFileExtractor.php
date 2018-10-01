<?php
/**
 * Author: Courtney Miles
 * Date: 1/10/18
 * Time: 7:58 PM
 */

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\ExtractorInterface;

class CsvMultiFileExtractor implements ExtractorInterface
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

    public function getIterator()
    {
        $iterator = new \AppendIterator();

        foreach ($this->extractors as $extractor) {
            $iterator->append($extractor->getIterator());
        }

        return $iterator;
    }

    protected function addExtractor(CsvFileExtractor $extractor)
    {
        $this->extractors[] = $extractor;
    }
}
