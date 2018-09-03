<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 7:08 PM
 */

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use CallbackFilterIterator;
use League\Csv\Reader;
use MilesAsylum\Slurp\Extract\ExtractorInterface;

class CsvFileExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $csvReader;

    private $headers = [];

    private $headerOffset;

    public function __construct(Reader $csvReader)
    {
        $this->csvReader = $csvReader;
    }

    /**
     * Loads the first row in the CSV file as the headers.
     */
    public function loadHeadersFromFile() : void
    {
        $this->headers = $this->csvReader->fetchOne();
        $this->headerOffset = 0;
    }

    public function setHeaders(array $headers) : void
    {
        $this->headers = $headers;
    }

    public function getIterator() : \Iterator
    {
        return $this->prepareRecords($this->csvReader->getRecords(), $this->headers);
    }

    protected function prepareRecords(\Iterator $records, array $headers): \Iterator
    {
        if ($this->headerOffset !== null) {
            $records = new CallbackFilterIterator($records, function (array $record, int $offset): bool {
                return $offset !== $this->headerOffset;
            });
        }

        $valueCount = !empty($headers) ? count($headers) : count($this->csvReader->fetchOne());
        $records = new VerifyValueCountIterator($records, $valueCount);

        if (!empty($headers)) {
            $records = new MapIterator($records, $headers);
        }

        return $records;
    }
}
