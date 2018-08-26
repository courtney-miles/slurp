<?php
/**
 * Author: Courtney Miles
 * Date: 25/08/18
 * Time: 6:49 PM
 */

namespace MilesAsylum\Slurp\Tests\functional;

use League\Csv\Reader;
use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Extract\CsvFileExtractor\CsvFileExtractor;
use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsUpdStmt;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsUpdQueryFactory;
use MilesAsylum\Slurp\PHPUnit\MySQLTestHelper;
use MilesAsylum\Slurp\SlurpBuilder;
use MilesAsylum\Slurp\Transform\StrCase;
use MilesAsylum\Slurp\Transform\Transformer;
use MilesAsylum\Slurp\Transform\TransformerLoader;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;

class SlurpTest extends TestCase
{
    use TestCaseTrait;

    protected static $pdo;

    protected static $table;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $m = new MySQLTestHelper();
        $m->raiseTestSchema();

        self::$pdo = $m->getConnection();
        self::$pdo->exec('USE ' . $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_DATABASE']);

        self::$table = 'tbl_foo';
        $table = self::$table;

        self::$pdo->exec(<<<SQL
CREATE TABLE `{$table}` (
  `name` VARCHAR(100) NOT NULL,
  `date` DATE NOT NULL,
  `value` DECIMAL(10,2) NOT NULL
) COLLATE ascii_general_ci
SQL
        );
    }

    public function testBasicLoad()
    {
        $sb = new SlurpBuilder(new PipelineBuilder(), new PipelineBuilder());
        $sb->addLoader(
            $this->createDatabaseLoader(1)
        );
        $cfe = new CsvFileExtractor(
            Reader::createFromPath(__DIR__ . '/csv/simple.csv')
        );
        $cfe->loadHeadersFromFile();
        $slurp = $sb->build();
        $slurp->process($cfe);

        $table = $this->fetchQueryTable('tbl_foo');
        $expectedTable = $this->createArrayDataSet(
            [
                self::$table => [
                    ['name' => 'foo', 'date' => '2018-01-01', 'value' => '123.00'],
                    ['name' => 'bar', 'date' => '2018-01-02', 'value' => '234.00'],
                    ['name' => 'baz', 'date' => '2018-01-03', 'value' => '345.00']
                ]
            ]
        )->getTable(self::$table);

        $this->assertTablesEqual($expectedTable, $table);
    }

    public function testBasicLoadUnevenBatch()
    {
        $sb = new SlurpBuilder(new PipelineBuilder(), new PipelineBuilder());
        $sb->addLoader(
            $this->createDatabaseLoader(2) // Batches of 2 will leave one row left over.
        );
        $cfe = new CsvFileExtractor(
            Reader::createFromPath(__DIR__ . '/csv/simple.csv')
        );
        $cfe->loadHeadersFromFile();
        $slurp = $sb->build();
        $slurp->process($cfe);

        $table = $this->fetchQueryTable('tbl_foo');
        $expectedTable = $this->createArrayDataSet(
            [
                self::$table => [
                    ['name' => 'foo', 'date' => '2018-01-01', 'value' => '123.00'],
                    ['name' => 'bar', 'date' => '2018-01-02', 'value' => '234.00'],
                    ['name' => 'baz', 'date' => '2018-01-03', 'value' => '345.00']
                ]
            ]
        )->getTable(self::$table);

        $this->assertTablesEqual($expectedTable, $table);
    }

    public function testBasicLoadWithTransform()
    {
        $t = new Transformer(new TransformerLoader());

        $sb = new SlurpBuilder(new PipelineBuilder(), new PipelineBuilder());
        $sb->addChange(
            'name',
            new StrCase(StrCase::CASE_UPPER),
            $t
        )->addLoader(
            $this->createDatabaseLoader(1)
        );

        $cfe = new CsvFileExtractor(
            Reader::createFromPath(__DIR__ . '/csv/simple.csv')
        );
        $cfe->loadHeadersFromFile();
        $slurp = $sb->build();
        $slurp->process($cfe);

        $table = $this->fetchQueryTable('tbl_foo');
        $expectedTable = $this->createArrayDataSet(
            [
                self::$table => [
                    ['name' => 'FOO', 'date' => '2018-01-01', 'value' => '123.00'],
                    ['name' => 'BAR', 'date' => '2018-01-02', 'value' => '234.00'],
                    ['name' => 'BAZ', 'date' => '2018-01-03', 'value' => '345.00']
                ]
            ]
        )->getTable('tbl_foo');

        $this->assertTablesEqual($expectedTable, $table);
    }

    protected function createDatabaseLoader($batchSize)
    {
        return new DatabaseLoader(
            new BatchInsUpdStmt(
                self::$pdo,
                self::$table,
                ['name', 'date', 'value'],
                new BatchInsUpdQueryFactory()
            ),
            $batchSize
        );
    }

    protected function fetchQueryTable($tableName)
    {
        return $this->getConnection()->createQueryTable(
            $tableName,
            <<<SQL
SELECT * FROM `{$tableName}`
SQL
        );
    }

    /**
     * Returns the test database connection.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection(self::$pdo, $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_DATABASE']);
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        return $this->createArrayDataSet(
            ['tbl_foo' => []]
        );
    }
}
