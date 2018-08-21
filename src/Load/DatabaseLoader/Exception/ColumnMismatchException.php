<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 10:57 PM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader\Exception;

use MilesAsylum\Slurp\Load\DatabaseLoader\Exception\ExceptionInterface;

class ColumnMismatchException extends \InvalidArgumentException implements ExceptionInterface
{
}
