<?php
/**
 * Author: Courtney Miles
 * Date: 25/08/18
 * Time: 6:49 PM
 */

namespace MilesAsylum\Slurp\Tests\functional;

use frictionlessdata\tableschema\Schema;
use League\Csv\Reader;
use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Extract\CsvFileExtractor\CsvFileExtractor;
use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsUpdStmt;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsUpdQueryFactory;
use MilesAsylum\Slurp\PHPUnit\MySQLTestHelper;
use MilesAsylum\Slurp\SlurpBuilder;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Transform\SlurpTransformer\StrCase;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\TransformerLoader;
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

        self::$pdo->exec("DROP TABLE IF EXISTS `{$table}`");
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
        $sb = SlurpBuilder::create();
        $sb->addLoader(
            $sb->createDatabaseLoader(
                self::$pdo,
                self::$table,
                ['name' => '_name_', 'date' => '_date_', 'value' => '_value_'],
                1
            )
        );
        $cfe = new CsvFileExtractor(
            Reader::createFromPath(__DIR__ . '/csv/simple.csv')
        );
        $cfe->loadHeadersFromFile();
        $slurp = $sb->build();
        $slurp->process($cfe);

        $table = $this->fetchQueryTable(self::$table);
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
        $sb = SlurpBuilder::create();
        $sb->addLoader(
            $sb->createDatabaseLoader(
                self::$pdo,
                self::$table,
                ['name' => '_name_', 'date' => '_date_', 'value' => '_value_'],
                2
            ) // Batches of 2 will leave one row left over.
        );
        $cfe = new CsvFileExtractor(
            Reader::createFromPath(__DIR__ . '/csv/simple.csv')
        );
        $cfe->loadHeadersFromFile();
        $slurp = $sb->build();
        $slurp->process($cfe);

        $table = $this->fetchQueryTable(self::$table);
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
        $sb = SlurpBuilder::create();
        $sb->addChange(
            '_name_',
            new StrCase(StrCase::CASE_UPPER)
        )->addLoader(
            $sb->createDatabaseLoader(
                self::$pdo,
                self::$table,
                ['name' => '_name_', 'date' => '_date_', 'value' => '_value_'],
                1
            )
        );

        $cfe = new CsvFileExtractor(
            Reader::createFromPath(__DIR__ . '/csv/simple.csv')
        );
        $cfe->loadHeadersFromFile();
        $slurp = $sb->build();
        $slurp->process($cfe);

        $table = $this->fetchQueryTable(self::$table);
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

    public function testAllTypesFromSchema()
    {
        self::$pdo->exec('DROP TABLE IF EXISTS `all_types`');
        self::$pdo->exec(<<<SQL
CREATE TABLE `all_types` (
  `a_string` VARCHAR(100) NOT NULL,
  `a_number` DECIMAL(10,2) NOT NULL,
  `an_integer` INT NOT NULL,
  `a_boolean` BOOL NOT NULL,
  `a_date` DATE NOT NULL,
  `a_time` TIME NOT NULL,
  `a_datetime` DATETIME NOT NULL
) COLLATE ascii_general_ci
SQL
        );

        $sb = SlurpBuilder::create();
        $slurp = $sb->setTableSchema(
            new Schema(__DIR__ . '/csv/all-types.schema.json')
        )->addLoader(
            $sb->createDatabaseLoader(
                self::$pdo,
                'all_types',
                array_combine(
                    ['a_string','a_number','an_integer','a_boolean','a_date','a_time','a_datetime'],
                    ['a_string','a_number','an_integer','a_boolean','a_date','a_time','a_datetime']
                ),
                1
            )
        )->build();

        $cfe = new CsvFileExtractor(
            Reader::createFromPath(__DIR__ . '/csv/all-types.csv')
        );
        $cfe->loadHeadersFromFile();
        $slurp->process($cfe);

        $table = $this->fetchQueryTable('all_types');
        $expectedTable = $this->createArrayDataSet(
            [
                'all_types' => [
                    [
                        'a_string' => 'foo',
                        'a_number' => 123.45,
                        'an_integer' => 234,
                        'a_boolean' => 1,
                        'a_date' => '2018-01-01',
                        'a_time' => '12:34:56',
                        'a_datetime' => '2018-01-01 12:34:56'
                    ],
                ]
            ]
        )->getTable('all_types');

        $this->assertTablesEqual($expectedTable, $table);
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
