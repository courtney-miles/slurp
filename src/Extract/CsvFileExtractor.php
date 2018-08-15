<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 7:08 PM
 */

namespace MilesAsylum\Slurp\Extract;


use MilesAsylum\Slurp\Extract\Exception\MalformedCsvException;

class CsvFileExtractor implements ExtractorInterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var bool|resource
     */
    protected $fileHandle;

    protected $columnNames = [];

    protected $firstRowValueCount;

    /**
     * @var int
     */
    private $lineLength;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure;

    /**
     * @var string
     */
    private $escape;

    /**
     * @var array
     */
    protected $currentLine;

    protected $currentLineNo = null;

    /**
     * CsvFileExtractor constructor.
     * @param $filePath
     * @param $hasHeader
     * @param int $lineLength
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @throws MalformedCsvException
     */
    public function __construct(
        $filePath,
        $hasHeader,
        $lineLength = 0,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\'
    ) {
        $this->filePath = $filePath;
        $this->fileHandle = fopen($this->filePath, "r");
        $this->lineLength = $lineLength;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;

        if ($hasHeader) {
            $this->columnNames = $this->loadCsvLine();

            if (count($this->columnNames) !== count(array_unique($this->columnNames))) {
                throw new MalformedCsvException(
                    "The loaded file {$this->filePath} contains duplicate column names."
                );
            }
        }

        $this->currentLine = $this->loadCsvLine();
        $this->firstRowValueCount = count($this->currentLine);
    }

    public function getColumnNames()
    {
        return $this->columnNames;
    }

    /**
     * @return array|false|null
     * @throws MalformedCsvException
     */
    public function current()
    {
        if (!empty($this->columnNames)) {
            if (count($this->columnNames) !== count($this->currentLine)) {
                throw new MalformedCsvException(
                    sprintf(
                        'Line %s in %s has %s values where we expected %s.',
                        $this->currentLineNo,
                        $this->filePath,
                        count($this->currentLine),
                        count($this->columnNames)
                    )
                );
            }
            return array_combine($this->columnNames, $this->currentLine);
        } elseif ($this->firstRowValueCount != count($this->currentLine)) {
            throw new MalformedCsvException(
                sprintf(
                    'Line %s in %s has %s values where previous rows had %s.',
                    $this->currentLineNo,
                    $this->filePath,
                    count($this->currentLine),
                    $this->firstRowValueCount
                )
            );
        }

        return $this->currentLine;
    }

    public function next()
    {
        $this->currentLine = $this->loadCsvLine();
    }

    public function key()
    {
        return $this->currentLineNo;
    }

    public function valid()
    {
        return $this->currentLine !== false;
    }

    public function rewind()
    {
        if ($this->currentLineNo > 1) {
            $this->currentLineNo = null;
            rewind($this->fileHandle);
            $this->loadCsvLine(); // Skip the line containing headers.
            $this->currentLine = $this->loadCsvLine();
        }
    }

    protected function loadCsvLine()
    {
        if ($this->currentLineNo === null) {
            $this->currentLineNo = 0;
        } else {
            $this->currentLineNo++;
        }

        return fgetcsv($this->fileHandle, $this->lineLength, $this->delimiter, $this->enclosure, $this->escape);
    }
}
