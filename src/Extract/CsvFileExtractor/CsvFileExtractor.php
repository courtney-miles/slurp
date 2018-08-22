<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 7:08 PM
 */

namespace MilesAsylum\Slurp\Extract\CsvFileExtractor;

use League\Csv\Reader;
use MilesAsylum\Slurp\Extract\ExtractorInterface;

class CsvFileExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $csvReader;

    private $headers = [];

    public function __construct(Reader $csvReader)
    {
        $this->csvReader = $csvReader;
    }

    /**
     * Loads the first row in the CSV file as the headers.
     * @throws \League\Csv\Exception
     */
    public function loadHeadersFromFile() : void
    {
        $this->csvReader->setHeaderOffset(0);
    }

    public function setHeaders(array $headers) : void
    {
        $this->headers = $headers;
    }

    public function getIterator() : \Iterator
    {
        return $this->csvReader->getRecords($this->headers);
    }
}
