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

namespace MilesAsylum\Slurp\Tests\functional;

use League\Csv\Reader;
use MilesAsylum\Slurp\Event\RecordValidatedEvent;
use MilesAsylum\Slurp\Extract\CsvFileExtractor\CsvFileExtractor;
use MilesAsylum\Slurp\PHPUnit\MySQLTestHelper;
use MilesAsylum\Slurp\SlurpBuilder;
use MilesAsylum\Slurp\Transform\SlurpTransformer\CallbackChange;
use MilesAsylum\Slurp\Validate\RecordViolation;
use Mockery;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\DataSet\ITable;
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        self::$pdo->exec('USE ' . $m->getDatabaseName());

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

    public function testBasicLoad(): void
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

        self::assertTablesEqual($expectedTable, $table);
    }

    public function testBasicLoadUnevenBatch(): void
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

        self::assertTablesEqual($expectedTable, $table);
    }

    public function testBasicLoadWithTransform(): void
    {
        $sb = SlurpBuilder::create();
        $sb->addTransformationChange(
            '_name_',
            new CallbackChange(
                static function ($value) {
                    return strtoupper($value);
                }
            )
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

        self::assertTablesEqual($expectedTable, $table);
    }

    public function testAllTypesFromSchema(): void
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
            $sb->createTableSchemaFromPath(__DIR__ . '/csv/all-types.schema.json')
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

        $cfe = CsvFileExtractor::createFromPath(__DIR__ . '/csv/all-types.csv');
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

        self::assertTablesEqual($expectedTable, $table);
    }

    public function testValidateAgainstSchemaWithMissingColumns(): void
    {
        $violations = [];
        $mockDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')->byDefault();
        $mockDispatcher->shouldReceive('dispatch')
            ->withArgs(static function (string $eventName, Event $event) use (&$violations) {
                if ($eventName === RecordValidatedEvent::NAME) {
                    /** @var RecordValidatedEvent $event */
                    $violations = array_merge($violations, $event->getPayload()->getViolations());
                    return true;
                }

                return false;
            });

        $cfe = CsvFileExtractor::createFromPath(__DIR__ . '/csv/missing-column.csv');
        $cfe->loadHeadersFromFile();

        $sb = SlurpBuilder::create();
        $slurp = $sb->setTableSchema(
            $sb->createTableSchemaFromArray(
                [
                   'fields' => [
                       ['name' => 'col_a'],
                       ['name' => 'col_b'],
                       ['name' => 'col_c'],
                   ]
                ]
            )
        )->setEventDispatcher($mockDispatcher)
            ->build();

        $slurp->process($cfe);

        $this->assertCount(1, $violations);
        $violation = array_pop($violations);
        $this->assertInstanceOf(RecordViolation::class, $violation);
    }

    protected function fetchQueryTable($tableName): ITable
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
    protected function getConnection(): Connection
    {
        return $this->createDefaultDBConnection(self::$pdo, $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_DATABASE']);
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet(): IDataSet
    {
        return $this->createArrayDataSet(
            ['tbl_foo' => []]
        );
    }
}
