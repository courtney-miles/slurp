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

namespace MilesAsylum\Slurp\Extract\DatabaseExtractor;

use MilesAsylum\Slurp\Extract\ExtractorInterface;
use Traversable;

class DatabaseExtractor implements ExtractorInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $queryParams;

    /**
     * @var \PDOStatement
     */
    private $queryStmt;

    public function __construct(\PDO $pdo, string $queryStr, array $queryParams = [])
    {
        $this->pdo = $pdo;
        $this->queryStmt = $this->pdo->prepare($queryStr);
        $this->queryParams = $queryParams;
    }

    public function getIterator(): iterable
    {
        $this->queryStmt->execute($this->queryParams);

        return new \IteratorIterator($this->queryStmt);
    }
}