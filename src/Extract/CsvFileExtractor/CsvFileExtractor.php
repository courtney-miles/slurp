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

use CallbackFilterIterator;
use Iterator;
use League\Csv\Exception;
use League\Csv\Reader;

class CsvFileExtractor implements CsvFileExtractorInterface
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

    public static function createFromPath(string $path): self
    {
        return new static(Reader::createFromPath($path));
    }

    /**
     * @param string $delimiter
     *
     * @throws Exception
     */
    public function setDelimiter(string $delimiter): void
    {
        $this->csvReader->setDelimiter($delimiter);
    }

    /**
     * @param string $enclosure
     *
     * @throws Exception
     */
    public function setEnclosure(string $enclosure): void
    {
        $this->csvReader->setEnclosure($enclosure);
    }

    /**
     * @param string $escape
     *
     * @throws Exception
     */
    public function setEscape(string $escape): void
    {
        $this->csvReader->setEscape($escape);
    }

    /**
     * Loads the first row in the CSV file as the headers.
     */
    public function loadHeadersFromFile(): void
    {
        $this->headers = $this->csvReader->fetchOne();
        $this->headerOffset = 0;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function getIterator(): Iterator
    {
        return $this->prepareRecords($this->csvReader->getRecords(), $this->headers);
    }

    protected function prepareRecords(Iterator $records, array $headers): Iterator
    {
        if (null !== $this->headerOffset) {
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
